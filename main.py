"""
Face Recognition System - Direct Draw Edition
=================================================
ระบบตรวจจับใบหน้า - วาดข้อความโดยตรงบนภาพกล้อง
-------------------------------------------------
- ข้อความใหญ่มาก (scale=4) เพื่อให้อ่านง่าย
- ควบคุมด้วยปุ่ม hardware
- วาด UI โดยตรงบนภาพ (ไม่ใช้ PipeLine OSD)
"""

from libs.PipeLine import PipeLine
from libs.AIBase import AIBase
from libs.AI2D import Ai2d
from libs.Utils import *
from media.sensor import Sensor
from media.media import *
from media.display import *
import os, gc, time, math
from machine import Pin
import ulab.numpy as np
import nncase_runtime as nn
import image
import aidemo
from umqtt.simple import MQTTClient
import ubinascii
import ujson

# ค่าคงที่ช่องสัญญาณกล้อง
CAM_CHN_ID_0 = 0  # ช่อง 0: สำหรับแสดงผล (Display)
CAM_CHN_ID_2 = 2  # ช่อง 2: สำหรับ AI ประมวลผล

# =========================
# ฟังก์ชันช่วยเหลือต่างๆ
# =========================
def ALIGN_UP(size, align):
    """จัด size ให้ตรงกับค่า align (เช่น 16-byte alignment)"""
    return (size + align - 1) // align * align

# =========================
# การตั้งค่า WiFi
# =========================
WIFI_CFG_FILE = "/sdcard/wifi_cfg.txt"  # ไฟล์ config WiFi
WIFI_TIMEOUT = 15  # รอเชื่อมต่อสูงสุด 15 วินาที

def load_wifi_config():
    """โหลดค่า config WiFi จากไฟล์ wifi_cfg.txt"""
    global WIFI_SSID, WIFI_PASSWORD

    try:
        with open(WIFI_CFG_FILE, 'r') as f:
            for line in f:
                line = line.strip()
                if line.startswith('#') or not line:
                    continue
                if '=' in line:
                    key, value = line.split('=', 1)
                    key = key.strip()
                    value = value.strip()
                    if key == 'WIFI_SSID':
                        WIFI_SSID = value
                    elif key == 'WIFI_PASSWORD':
                        WIFI_PASSWORD = value
        print(f"WiFi config loaded: SSID={WIFI_SSID}")
        return True
    except Exception as e:
        print(f"Failed to load wifi_cfg.txt: {e}")
        print("Using default credentials")
        WIFI_SSID = "CMMC_AIS_2.4G"
        WIFI_PASSWORD = "zxc12345"
        return False

# โหลด config เมื่อเริ่มต้น
WIFI_SSID = ""
WIFI_PASSWORD = ""
load_wifi_config()

def wifi_connect():
    """เชื่อมต่อ WiFi (หมายเหตุ: K230 รองรับเฉพาะ 2.4G เท่านั้น!)"""
    import network
    from machine import Pin

    print("=" * 50)
    print("WiFi Connection Starting...")
    print("=" * 50)

    wlan = network.WLAN(network.STA_IF)  # Station mode
    wlan.active(True)

    start_time = time.time()

    if not wlan.isconnected():
        print(f"Connecting to: {WIFI_SSID}")
        print("NOTE: K230 supports 2.4G WiFi only!")
        wlan.connect(WIFI_SSID, WIFI_PASSWORD)

        # รอให้เชื่อมต่อสำเร็จ พร้อม timeout
        while not wlan.isconnected():
            if time.ticks_diff(time.ticks_ms(), time.ticks_ms() * 1000) > WIFI_TIMEOUT * 1000:
            # ทางเลือก: ใช้ if time.time() - start_time > WIFI_TIMEOUT:
                print(f"WIFI Timeout ({WIFI_TIMEOUT}s)!")
                return False, None
            time.sleep_ms(500)

    if wlan.isconnected():
        ip, subnet, gateway, dns = wlan.ifconfig()
        print("=" * 50)
        print("WIFI Connected Successfully!")
        print(f"IP:      {ip}")
        print(f"Subnet:  {subnet}")
        print(f"Gateway: {gateway}")
        print(f"DNS:     {dns}")
        print("=" * 50)
        return True, ip
    else:
        print("WIFI Connection Failed!")
        print("Please check:")
        print("  1. SSID is correct")
        print("  2. Password is correct")
        print("  3. WiFi is 2.4G (K230 doesn't support 5G!)")
        return False, None

# =========================
# ฟังก์ชันตรวจสอบ Network
# =========================
def check_network():
    """ตรวจสอบว่ามี network อยู่หรือไม่ และคืนค่า IP"""
    try:
        import network
        wlan = network.WLAN(network.STA_IF)
        if wlan.active():
            ip, subnet, gateway, dns = wlan.ifconfig()
            if ip != '0.0.0.0':
                print(f"Network: Connected - IP: {ip}")
                return True, ip
            else:
                print("Network: Not connected (IP is 0.0.0.0)")
                return False, None
        else:
            print("Network: WiFi not active")
            return False, None
    except Exception as e:
        print(f"Network check error: {e}")
        return False, None

def test_mqtt_connection(broker, port, timeout=5):
    """ทดสอบว่าเชื่อมต่อไป MQTT Broker ได้หรือไม่"""
    try:
        import socket
        sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        sock.settimeout(timeout)
        result = sock.connect_ex((broker, port))
        sock.close()
        if result == 0:
            print(f"MQTT Broker ({broker}:{port}) - REACHABLE!")
            return True
        else:
            print(f"MQTT Broker ({broker}:{port}) - NOT REACHABLE!")
            return False
    except Exception as e:
        print(f"Connection test error: {e}")
        return False

# =========================
# คลาส MQTT Client
# =========================
class MQTTClientWrapper:
    """
    คลาสสำหรับจัดการการเชื่อมต่อ MQTT
    - เชื่อมต่อ/ตัดการเชื่อมต่อ
    - ส่งข้อมูล (publish)
    - ตรวจสอบสถานะและเชื่อมต่อใหม่อัตโนมัติ
    """
    def __init__(self, broker, port, user, password, client_id, keepalive=60):
        self.broker = broker
        self.port = port
        self.user = user
        self.password = password
        self.client_id = client_id
        self.keepalive = keepalive
        self.client = None
        self.connected = False
        self.last_connect_attempt = 0
        self.reconnect_interval = 5000  # 5 วินาที

    def connect(self):
        """เชื่อมต่อไป MQTT Broker"""
        try:
            print(f"MQTT: กำลังเชื่อมต่อ {self.broker}:{self.port}...")
            self.client = MQTTClient(
                self.client_id,
                self.broker,
                self.port,
                self.user,
                self.password,
                self.keepalive
            )
            self.client.connect()
            self.connected = True
            print("MQTT: เชื่อมต่อสำเร็จ!")
            return True
        except Exception as e:
            print(f"MQTT: เชื่อมต่อไม่สำเร็จ - {e}")
            self.connected = False
            return False

    def disconnect(self):
        """ตัดการเชื่อมต่อ MQTT"""
        if self.client and self.connected:
            try:
                self.client.disconnect()
                self.connected = False
                print("MQTT: ตัดการเชื่อมต่อแล้ว")
            except:
                pass

    def publish(self, topic, payload, qos=1):
        """ส่งข้อมูลออก MQTT"""
        if not self.connected:
            return False
        try:
            self.client.publish(topic, payload, qos=qos)
            return True
        except Exception as e:
            print(f"MQTT: ส่งข้อมูลไม่สำเร็จ - {e}")
            self.connected = False
            return False

    def publish_data(self, topic_data, recognition_result, detected_faces=0):
        """
        ============================================================
        ส่งข้อมูล face recognition ออก MQTT (แบบ JSON)
        ============================================================
        ส่ง: timestamp, device_id, ผลลัพธ์การจดจำ, จำนวนใบหน้า
        """
        data = {
            "timestamp": time.ticks_ms(),
            "device": self.client_id,
            "recognition": recognition_result,  # เช่น "ID:001 0.95" หรือ "unknown"
            "faces_detected": detected_faces,
            "status": "detected" if detected_faces > 0 else "no_face"
        }
        try:
            json_data = ujson.dumps(data)
            return self.publish(topic_data, json_data, qos=1)
        except Exception as e:
            print(f"MQTT: JSON error - {e}")
            return False

    def publish_image(self, topic_image, img):
        """
        ============================================================
        ส่งภาพเต็มออก MQTT (base64)
        ============================================================
        ใช้ถ้าอยากส่งภาพเต็ม ไม่ครอป
        หมายเหตุ: ตอนนี้ระบบใช้ publish_cropped_face() แทน
        """
        try:
            # แปลงภาพเป็น JPEG bytes
            img_bytes = img.to_jpeg(quality=80)
            # แปลงเป็น base64 string
            base64_data = ubinascii.b2a_base64(img_bytes).decode().strip()
            # ส่งออก MQTT
            return self.publish(topic_image, base64_data, qos=1)
        except Exception as e:
            print(f"MQTT: Image publish failed - {e}")
            return False

    def publish_cropped_face(self, topic_image, img_display, det_boxes, rgb888p_size, display_size):
        try:
            if det_boxes and len(det_boxes) > 0:
                det = det_boxes[0]
                if len(det) >= 4:
                    # 1. คำนวณพิกัด (X, Y, W, H)
                    x1 = int(det[0] * display_size[0] / rgb888p_size[0])
                    y1 = int(det[1] * display_size[1] / rgb888p_size[1])
                    w = int(det[2] * display_size[0] / rgb888p_size[0])
                    h = int(det[3] * display_size[1] / rgb888p_size[1])

                    # 2. ป้องกันพิกัดออกนอกขอบจอ (สำคัญมาก!)
                    x1 = max(0, min(x1, display_size[0] - 1))
                    y1 = max(0, min(y1, display_size[1] - 1))
                    w = min(w, display_size[0] - x1)
                    h = min(h, display_size[1] - y1)

                    # 3. ลองส่งภาพแบบ Crop ตรงๆ (ไม่เพิ่ม Padding ไม่ Resize)
                    # ถ้าค่า W หรือ H เป็น 0 บอร์ดจะค้าง ต้องเช็คก่อน
                    if w > 10 and h > 10:
                        cropped_face = img_display.copy((x1, y1, w, h))
                        img_bytes = cropped_face.to_jpeg(quality=50) # ลดคุณภาพลงเพื่อความไว
                        
                        base64_data = ubinascii.b2a_base64(img_bytes).decode().strip()
                        payload = ujson.dumps({"image": base64_data}) # ใช้ Key 'image' ให้ตรงกับ Bridge
                        
                        self.publish(topic_image, payload, qos=1)
                        print(f"📸 [CROP SENT] Size: {w}x{h}")
                        
                        del cropped_face
                        gc.collect()
                        return True
            return False
        except Exception as e:
            print(f"MQTT Crop Error: {e}")
            return False

    def check_and_reconnect(self):
        """ตรวจสอบการเชื่อมต่อ และเชื่อมต่อใหม่ถ้าขาด"""
        now = time.ticks_ms()
        if not self.connected and time.ticks_diff(now, self.last_connect_attempt) > self.reconnect_interval:
            self.last_connect_attempt = now
            return self.connect()
        return self.connected

# =========================
# การตั้งค่าจอแสดงผลและกล้อง
# =========================
DISPLAY_WIDTH = 640    # ความกว้างจอแสดงผล
DISPLAY_HEIGHT = 480   # ความสูงจอแสดงผล
RGB888P_SIZE = [640, 480]  # ขนาดภาพสำหรับ AI ประมวลผล

# =========================
# MQTT Configuration
# =========================
# --------------------------------------------------------
# ตั้งค่าการเชื่อมต่อ MQTT Broker
# --------------------------------------------------------
MQTT_BROKER = "178.128.49.254"       # IP ของ MQTT Broker
MQTT_PORT = 1883                     # Port มาตรฐาน
MQTT_USER = "Apinun"                 # Username
MQTT_PASSWORD = ""                   # Password (ว่าง = ไม่มี)
MQTT_KEEPALIVE = 60                  # เก็บ connection ไว้ 60 วินาที

# --------------------------------------------------------
# Client ID สร้างอัตโนมัติ (ไม่ซ้ำกัน)
# --------------------------------------------------------
MQTT_CLIENT_ID = "K230_FaceRec_" + ubinascii.hexlify(os.urandom(3)).decode()

# --------------------------------------------------------
# Topics สำหรับส่งข้อมูล
# --------------------------------------------------------
MQTT_TOPIC_DATA = "RCN/ID001/DATA"    # ส่งข้อมูล face recognition (JSON)
MQTT_TOPIC_IMAGE = "RCN/ID001/image"  # ส่งรูปภาพใบหน้าที่ครอปแล้ว (base64)
MQTT_QOS = 1                          # Quality of Service (1 = รับประกันถึง)

# =========================
# พาธไฟล์ Model
# =========================
FACE_DET_KMODEL_PATH = "/sdcard/examples/kmodel/face_detection_320.kmodel"  # Model ตรวจจับใบหน้า
FACE_REG_KMODEL_PATH = "/sdcard/examples/kmodel/face_recognition.kmodel"    # Model จดจำใบหน้า
ANCHORS_PATH = "/sdcard/examples/utils/prior_data_320.bin"                 # ไฟล์ anchors
DATABASE_DIR = "/sdcard/examples/utils/db/"                                # โฟลเดอร์เก็บฐานข้อมูลใบหน้า

# =========================
# การตั้งค่า Model
# =========================
FACE_DET_INPUT_SIZE = [320, 320]   # ขนาด input สำหรับตรวจจับใบหน้า
FACE_REG_INPUT_SIZE = [112, 112]   # ขนาด input สำหรับจดจำใบหน้า
CONFIDENCE_THRESHOLD = 0.3         # ค่า threshold สำหรับความมั่นใจ (ลดจาก 0.5 → ให้ไวขึ้น)
NMS_THRESHOLD = 0.2                # ค่า threshold สำหรับ NMS (Non-Maximum Suppression)
FACE_RECOGNITION_THRESHOLD = 0.75 # ค่า threshold สำหรับการจดจำใบหน้า
ANCHOR_LEN = 4200                  # ความยาวของ anchors
DET_DIM = 4                        # มิติของ detection box

# =========================
# คลาสตรวจจับใบหน้า (Face Detection)
# =========================
class FaceDetApp(AIBase):
    """
    คลาสสำหรับตรวจจับใบหน้าในภาพ
    - ใช้ AI model ในการตรวจจับ
    - คืนค่า: bounding box ของใบหน้าที่พบ
    """
    def __init__(self,kmodel_path,model_input_size,anchors,confidence_threshold=0.25,nms_threshold=0.3,rgb888p_size=[1920,1080],display_size=[1920,1080],debug_mode=0):
        super().__init__(kmodel_path,model_input_size,rgb888p_size,debug_mode)
        self.kmodel_path=kmodel_path
        self.model_input_size=model_input_size
        self.confidence_threshold=confidence_threshold
        self.nms_threshold=nms_threshold
        self.anchors=anchors
        self.rgb888p_size=[ALIGN_UP(rgb888p_size[0],16),rgb888p_size[1]]
        self.display_size=[ALIGN_UP(display_size[0],16),display_size[1]]
        self.debug_mode=debug_mode
        self.ai2d=Ai2d(debug_mode)
        self.ai2d.set_ai2d_dtype(nn.ai2d_format.NCHW_FMT,nn.ai2d_format.NCHW_FMT,np.uint8, np.uint8)

    def config_preprocess(self,input_image_size=None):
        with ScopedTiming("set preprocess config",self.debug_mode > 0):
            ai2d_input_size=input_image_size if input_image_size else self.rgb888p_size
            top, bottom, left, right,_ =letterbox_pad_param(self.rgb888p_size,self.model_input_size)
            self.ai2d.pad([0, 0, 0, 0, top, bottom, left, right], 0, [104, 117, 123])
            self.ai2d.resize(nn.interp_method.tf_bilinear, nn.interp_mode.half_pixel)
            self.ai2d.build([1,3,ai2d_input_size[1],ai2d_input_size[0]],[1,3,self.model_input_size[1],self.model_input_size[0]])

    def postprocess(self,results):
        with ScopedTiming("postprocess",self.debug_mode > 0):
            res = aidemo.face_det_post_process(self.confidence_threshold,self.nms_threshold,self.model_input_size[0],self.anchors,self.rgb888p_size,results)
            if len(res)==0:
                return res,res
            else:
                return res[0],res[1]

# =========================
# คลาสลงทะเบียนใบหน้า (Face Registration)
# =========================
class FaceRegistrationApp(AIBase):
    """
    คลาสสำหรับลงทะเบียนใบหน้า
    - แปลง landmark ของใบหน้าเป็น feature vector
    - ใช้สำหรับเปรียบเทียบใบหน้า
    """
    def __init__(self,kmodel_path,model_input_size,rgb888p_size=[1920,1080],display_size=[1920,1080],debug_mode=0):
        super().__init__(kmodel_path,model_input_size,rgb888p_size,debug_mode)
        self.kmodel_path=kmodel_path
        self.model_input_size=model_input_size
        self.rgb888p_size=[ALIGN_UP(rgb888p_size[0],16),rgb888p_size[1]]
        self.display_size=[ALIGN_UP(display_size[0],16),display_size[1]]
        self.debug_mode=debug_mode
        self.umeyama_args_112 = [
            38.2946 , 51.6963 ,
            73.5318 , 51.5014 ,
            56.0252 , 71.7366 ,
            41.5493 , 92.3655 ,
            70.7299 , 92.2041
        ]
        self.ai2d=Ai2d(debug_mode)
        self.ai2d.set_ai2d_dtype(nn.ai2d_format.NCHW_FMT,nn.ai2d_format.NCHW_FMT,np.uint8, np.uint8)

    def config_preprocess(self,landm,input_image_size=None):
        with ScopedTiming("set preprocess config",self.debug_mode > 0):
            ai2d_input_size=input_image_size if input_image_size else self.rgb888p_size
            affine_matrix = self.get_affine_matrix(landm)
            self.ai2d.affine(nn.interp_method.cv2_bilinear,0, 0, 127, 1,affine_matrix)
            self.ai2d.build([1,3,ai2d_input_size[1],ai2d_input_size[0]],[1,3,self.model_input_size[1],self.model_input_size[0]])

    def postprocess(self,results):
        with ScopedTiming("postprocess",self.debug_mode > 0):
            return results[0][0]

    def svd22(self,a):
        s = [0.0, 0.0]
        u = [0.0, 0.0, 0.0, 0.0]
        v = [0.0, 0.0, 0.0, 0.0]
        s[0] = (math.sqrt((a[0] - a[3]) ** 2 + (a[1] + a[2]) ** 2) + math.sqrt((a[0] + a[3]) ** 2 + (a[1] - a[2]) ** 2)) / 2
        s[1] = abs(s[0] - math.sqrt((a[0] - a[3]) ** 2 + (a[1] + a[2]) ** 2))
        v[2] = math.sin((math.atan2(2 * (a[0] * a[1] + a[2] * a[3]), a[0] ** 2 - a[1] ** 2 + a[2] ** 2 - a[3] ** 2)) / 2) if \
        s[0] > s[1] else 0
        v[0] = math.sqrt(1 - v[2] ** 2)
        v[1] = -v[2]
        v[3] = v[0]
        u[0] = -(a[0] * v[0] + a[1] * v[2]) / s[0] if s[0] != 0 else 1
        u[2] = -(a[2] * v[0] + a[3] * v[2]) / s[0] if s[0] != 0 else 0
        u[1] = (a[0] * v[1] + a[1] * v[3]) / s[1] if s[1] != 0 else -u[2]
        u[3] = (a[2] * v[1] + a[3] * v[3]) / s[1] if s[1] != 0 else u[0]
        v[0] = -v[0]
        v[2] = -v[2]
        return u, s, v

    def image_umeyama_112(self,src):
        SRC_NUM = 5
        SRC_DIM = 2
        src_mean = [0.0, 0.0]
        dst_mean = [0.0, 0.0]
        for i in range(0,SRC_NUM * 2,2):
            src_mean[0] += src[i]
            src_mean[1] += src[i + 1]
            dst_mean[0] += self.umeyama_args_112[i]
            dst_mean[1] += self.umeyama_args_112[i + 1]
        src_mean[0] /= SRC_NUM
        src_mean[1] /= SRC_NUM
        dst_mean[0] /= SRC_NUM
        dst_mean[1] /= SRC_NUM
        src_demean = [[0.0, 0.0] for _ in range(SRC_NUM)]
        dst_demean = [[0.0, 0.0] for _ in range(SRC_NUM)]
        for i in range(SRC_NUM):
            src_demean[i][0] = src[2 * i] - src_mean[0]
            src_demean[i][1] = src[2 * i + 1] - src_mean[1]
            dst_demean[i][0] = self.umeyama_args_112[2 * i] - dst_mean[0]
            dst_demean[i][1] = self.umeyama_args_112[2 * i + 1] - dst_mean[1]
        A = [[0.0, 0.0], [0.0, 0.0]]
        for i in range(SRC_DIM):
            for k in range(SRC_DIM):
                for j in range(SRC_NUM):
                    A[i][k] += dst_demean[j][i] * src_demean[j][k]
                A[i][k] /= SRC_NUM
        T = [[1, 0, 0], [0, 1, 0], [0, 0, 1]]
        U, S, V = self.svd22([A[0][0], A[0][1], A[1][0], A[1][1]])
        T[0][0] = U[0] * V[0] + U[1] * V[2]
        T[0][1] = U[0] * V[1] + U[1] * V[3]
        T[1][0] = U[2] * V[0] + U[3] * V[2]
        T[1][1] = U[2] * V[1] + U[3] * V[3]
        scale = 1.0
        src_demean_mean = [0.0, 0.0]
        src_demean_var = [0.0, 0.0]
        for i in range(SRC_NUM):
            src_demean_mean[0] += src_demean[i][0]
            src_demean_mean[1] += src_demean[i][1]
        src_demean_mean[0] /= SRC_NUM
        src_demean_mean[1] /= SRC_NUM
        for i in range(SRC_NUM):
            src_demean_var[0] += (src_demean_mean[0] - src_demean[i][0]) * (src_demean_mean[0] - src_demean[i][0])
            src_demean_var[1] += (src_demean_mean[1] - src_demean[i][1]) * (src_demean_mean[1] - src_demean[i][1])
        src_demean_var[0] /= SRC_NUM
        src_demean_var[1] /= SRC_NUM
        scale = 1.0 / (src_demean_var[0] + src_demean_var[1]) * (S[0] + S[1])
        T[0][2] = dst_mean[0] - scale * (T[0][0] * src_mean[0] + T[0][1] * src_mean[1])
        T[1][2] = dst_mean[1] - scale * (T[1][0] * src_mean[0] + T[1][1] * src_mean[1])
        T[0][0] *= scale
        T[0][1] *= scale
        T[1][0] *= scale
        T[1][1] *= scale
        return T

    def get_affine_matrix(self,sparse_points):
        with ScopedTiming("get_affine_matrix", self.debug_mode > 1):
            matrix_dst = self.image_umeyama_112(sparse_points)
            matrix_dst = [matrix_dst[0][0],matrix_dst[0][1],matrix_dst[0][2],
                          matrix_dst[1][0],matrix_dst[1][1],matrix_dst[1][2]]
            return matrix_dst

# =========================
# ระบบจดจำใบหน้า (Face Recognition System)
# =========================
class FaceRecognition:
    """
    คลาสหลักของระบบจดจำใบหน้า
    - รวม Face Detection + Face Registration
    - จัดการฐานข้อมูลใบหน้า
    - ค้นหาใบหน้าที่จดจำได้
    """
    def __init__(self,face_det_kmodel,face_reg_kmodel,det_input_size,reg_input_size,database_dir,anchors,confidence_threshold=0.25,nms_threshold=0.3,face_recognition_threshold=0.75,rgb888p_size=[1280,720],display_size=[1920,1080],debug_mode=0):
        self.face_det_kmodel=face_det_kmodel
        self.face_reg_kmodel=face_reg_kmodel
        self.det_input_size=det_input_size
        self.reg_input_size=reg_input_size
        self.database_dir=database_dir
        self.anchors=anchors
        self.confidence_threshold=confidence_threshold
        self.nms_threshold=nms_threshold
        self.face_recognition_threshold=face_recognition_threshold
        self.rgb888p_size=[ALIGN_UP(rgb888p_size[0],16),rgb888p_size[1]]
        self.display_size=[ALIGN_UP(display_size[0],16),display_size[1]]
        self.debug_mode=debug_mode
        self.max_register_face = 100
        self.feature_num = 128
        self.valid_register_face = 0
        self.registered_count = 0
        self.next_id = 1
        self.db_name= []
        self.db_data= []
        self.face_det=FaceDetApp(self.face_det_kmodel,model_input_size=self.det_input_size,anchors=self.anchors,confidence_threshold=self.confidence_threshold,nms_threshold=self.nms_threshold,rgb888p_size=self.rgb888p_size,display_size=self.display_size,debug_mode=0)
        self.face_reg=FaceRegistrationApp(self.face_reg_kmodel,model_input_size=self.reg_input_size,rgb888p_size=self.rgb888p_size,display_size=self.display_size)
        self.face_det.config_preprocess()
        self.database_init()

    def run(self,input_np):
        det_boxes,landms=self.face_det.run(input_np)
        features = []
        for landm in landms:
            self.face_reg.config_preprocess(landm)
            feature=self.face_reg.run(input_np)
            features.append(feature)
        return det_boxes,landms,features

    def database_init(self):
        with ScopedTiming("database_init", self.debug_mode > 1):
            try:
                os.mkdir(self.database_dir)
            except OSError:
                pass

            db_file_list = os.listdir(self.database_dir)
            max_id = 0
            for db_file in db_file_list:
                if not db_file.endswith('.bin'):
                    continue
                if self.valid_register_face >= self.max_register_face:
                    break

                full_db_file = self.database_dir + db_file
                with open(full_db_file, 'rb') as f:
                    data = f.read()
                feature = np.frombuffer(data, dtype=np.float)
                self.db_data.append(feature)

                name = db_file.split('.')[0]
                try:
                    id_num = int(name)
                    if id_num > max_id:
                        max_id = id_num
                except ValueError:
                    pass

                self.db_name.append(name)
                self.valid_register_face += 1

            self.next_id = max_id + 1
            self.registered_count = self.valid_register_face

    def database_search(self,feature):
        with ScopedTiming("database_search", self.debug_mode > 1):
            if self.valid_register_face == 0:
                return 'unknown'

            v_id = -1
            v_score_max = 0.0
            feature /= np.linalg.norm(feature)
            for i in range(self.valid_register_face):
                db_feature = self.db_data[i]
                db_feature /= np.linalg.norm(db_feature)
                v_score = np.dot(feature, db_feature)/2 + 0.5
                if v_score > v_score_max:
                    v_score_max = v_score
                    v_id = i
            if v_id == -1:
                return 'unknown'
            elif v_score_max < self.face_recognition_threshold:
                return 'unknown'
            else:
                result = f'ID:{self.db_name[v_id]} {v_score_max:.2f}'
                return result

# =========================
# โปรแกรมหลัก (MAIN)
# =========================
if __name__=="__main__":
    from media.sensor import Sensor

    # การตั้งค่า
    rgb888p_size = RGB888P_SIZE
    display_size = [DISPLAY_WIDTH, DISPLAY_HEIGHT]

    # โหลด anchors สำหรับ face detection
    anchors = np.fromfile(ANCHORS_PATH, dtype=np.float)
    anchors = anchors.reshape((ANCHOR_LEN, DET_DIM))

    # ปุ่ม hardware (Pin 21)
    KEY = Pin(21, Pin.IN, Pin.PULL_UP)

    # ===== เชื่อมต่อ WiFi =====
    wifi_ok, device_ip = wifi_connect()

    # ===== WIFI CONNECTION =====
    wifi_ok, device_ip = wifi_connect()

    if not wifi_ok:
        print("ERROR: ไม่สามารถเชื่อมต่อ WiFi ได้!")
        print("MQTT จะไม่ทำงานหากไม่มี internet")
        print("ดำเนินการต่อไป...")

    # ===== ตรวจสอบการเชื่อมต่อ Network =====
    print("========================================")
    print("กำลังตรวจสอบการเชื่อมต่อ Network...")
    print("========================================")
    network_ok, device_ip = check_network()

    if network_ok:
        print(f"IP ของอุปกรณ์: {device_ip}")
        # ทดสอบการเชื่อมต่อ MQTT Broker
        mqtt_reachable = test_mqtt_connection(MQTT_BROKER, MQTT_PORT)
        if not mqtt_reachable:
            print("WARNING: ไม่สามารถเชื่อมต่อ MQTT Broker ได้!")
            print("กรุณาตรวจสอบ WiFi connection และ broker address")
    else:
        print("ERROR: ไม่มีการเชื่อมต่อ network!")
        print("กรุณาเชื่อมต่อ WiFi ก่อน")
    print("========================================")

    # ===== เริ่มต้น MQTT =====
    print("กำลังเริ่มต้น MQTT...")
    mqtt = MQTTClientWrapper(
        broker=MQTT_BROKER,
        port=MQTT_PORT,
        user=MQTT_USER,
        password=MQTT_PASSWORD,
        client_id=MQTT_CLIENT_ID,
        keepalive=MQTT_KEEPALIVE
    )
    mqtt.connect()
    mqtt_enabled = True
    last_mqtt_publish = 0
    mqtt_publish_interval = 1000  # ส่งทุกๆ 1 วินาที (จำกัดอัตราส่ง)

    try:
        # ===== เริ่มต้น Sensor (กล้อง) =====
        print("กำลังเริ่มต้น sensor...")
        sensor = Sensor(id=0)
        sensor.reset()

        # ตั้งค่า Dual channel (เหมือน test.py)
        # CHN_ID_0: สำหรับแสดงผล (RGB888)
        sensor.set_framesize(width=DISPLAY_WIDTH, height=DISPLAY_HEIGHT, chn=CAM_CHN_ID_0)
        sensor.set_pixformat(Sensor.RGB888, chn=CAM_CHN_ID_0)

        # CHN_ID_2: สำหรับ AI ประมวลผล (RGBP888 - aligned สำหรับ AI)
        sensor.set_framesize(w=rgb888p_size[0], h=rgb888p_size[1], chn=CAM_CHN_ID_2)
        sensor.set_pixformat(Sensor.RGBP888, chn=CAM_CHN_ID_2)

        # ===== เริ่มต้นจอแสดงผล =====
        print("กำลังเริ่มต้น display...")
        Display.init(Display.ST7701, width=DISPLAY_WIDTH, height=DISPLAY_HEIGHT, to_ide=True)
        MediaManager.init()

        sensor.run()
        print("เริ่มต้นระบบเรียบร้อย")

        # ===== เริ่มต้นระบบจดจำใบหน้า =====
        print("กำลังเริ่มต้น face recognition...")
        fr = FaceRecognition(
            FACE_DET_KMODEL_PATH,
            FACE_REG_KMODEL_PATH,
            det_input_size=FACE_DET_INPUT_SIZE,
            reg_input_size=FACE_REG_INPUT_SIZE,
            database_dir=DATABASE_DIR,
            anchors=anchors,
            confidence_threshold=CONFIDENCE_THRESHOLD,
            nms_threshold=NMS_THRESHOLD,
            face_recognition_threshold=FACE_RECOGNITION_THRESHOLD,
            rgb888p_size=rgb888p_size,
            display_size=display_size
        )

        # รับ ID เริ่มต้นจากฐานข้อมูล
        next_id = fr.next_id
        if next_id > 20:
            next_id = 1

        # ตัวแปรสถานะต่างๆ
        last_key = 1
        key_press_time = 0
        last_button_time = 0
        last_features = []
        register_status = ""
        target_status = f"Target: ID {next_id:03d}"
        status_clear_time = 0

        print("ระบบพร้อมใช้งาน!")
        print("========================================")
        print("  DIRECT DRAW MODE (ไม่ใช้ PipeLine OSD)")
        print("  ข้อความใหญ่มาก (scale=4)")
        print("========================================")
        print("วิธีควบคุม:")
        print("  - กดสั้น (< 1.5 วินาที): เปลี่ยน ID")
        print("  - กดยาว (> 1.5 วินาที): บันทึกใบหน้า")
        print("")

        # ===== ลูปหลัก (MAIN LOOP) =====
        while True:
            now = time.ticks_ms()

            # ===== จัดการปุ่ม hardware =====
            key_now = KEY.value()

            if last_key == 1 and key_now == 0:
                key_press_time = now

            elif last_key == 0 and key_now == 1:
                press_duration = time.ticks_diff(now, key_press_time)

                if press_duration < 1500:
                    # กดสั้น = เลือก ID ถัดไป
                    next_id = next_id + 1
                    if next_id > 20:
                        next_id = 1
                    target_status = f"Target: ID {next_id:03d}"
                    print(f"Target ID: {next_id}")
                    last_button_time = now

                elif press_duration >= 1500:
                    # กดยาว = บันทึกใบหน้า
                    if len(last_features) > 0:
                        person_id = f"{next_id:03d}"
                        filename = fr.database_dir + person_id + ".bin"
                        feature_bytes = last_features[0].tobytes()

                        with open(filename, 'wb') as f:
                            f.write(feature_bytes)

                        fr.registered_count += 1
                        fr.db_name.append(person_id)
                        fr.db_data.append(last_features[0])
                        fr.valid_register_face += 1

                        register_status = f"Saved: ID {person_id}"
                        status_clear_time = now

                        next_id = next_id + 1
                        if next_id > 20:
                            next_id = 1
                        target_status = f"Target: ID {next_id:03d}"

                        print(f"บันทึกแล้ว: ID {person_id}")
                    else:
                        register_status = "No face detected!"
                        status_clear_time = now
                        print("ไม่พบใบหน้า!")

                    last_button_time = now

            last_key = key_now

            # ===== การประมวลผลกล้องและ AI =====
            try:
                # รับภาพจากกล้องช่อง display (สำหรับ UI)
                img_display = sensor.snapshot(chn=CAM_CHN_ID_0)

                # รับภาพจากกล้องช่อง AI (สำหรับประมวลผล)
                img_ai = sensor.snapshot(chn=CAM_CHN_ID_2)

                # แปลงเป็น numpy สำหรับ AI ประมวลผล
                img_np = img_ai.to_numpy_ref()

                # รัน face detection และ face recognition
                det_boxes, landms, features = fr.run(img_np)
                last_features = features

                # รับผลลัพธ์การจดจำใบหน้า
                recg_results = []
                for feature in features:
                    res = fr.database_search(feature)
                    recg_results.append(res)
                    # แสดงผลลัพธ์การจดจำ (สำหรับ debug)
                    print(f"จดจำได้: {res}")

                # ===== MQTT PUBLISH SECTION =====
                # --------------------------------------------------------
                # เช็คการเชื่อมต่อ MQTT และส่งข้อมูลออกไป
                # --------------------------------------------------------

                # Reconnect if needed
                if not mqtt.connected:
                    mqtt.check_and_reconnect()

                # Publish face recognition data (throttled - ส่งทุกๆ 1 วินาที)
                if mqtt.connected and time.ticks_diff(now, last_mqtt_publish) > mqtt_publish_interval:
                    last_mqtt_publish = now

                    # ----------------------------------------------------
                    # ส่วนที่ 1: ส่งข้อมูล face recognition (JSON)
                    # ----------------------------------------------------
                    if recg_results and len(recg_results) > 0:
                        recog_text = recg_results[0]  # เช่น "ID:001 0.95"
                        mqtt.publish_data(MQTT_TOPIC_DATA, recog_text, detected_faces=len(features))
                    else:
                        mqtt.publish_data(MQTT_TOPIC_DATA, "unknown", detected_faces=0)

                    # ----------------------------------------------------
                    # ส่วนที่ 2: ส่งภาพใบหน้าที่ครอปแล้ว (base64)
                    # ----------------------------------------------------
                    # ครอปเฉพาะใบหน้า → แปลงเป็น JPEG → แปลงเป็น base64 → ส่งออก MQTT
                    if det_boxes and len(det_boxes) > 0:
                        mqtt.publish_cropped_face(MQTT_TOPIC_IMAGE, img_display, det_boxes, fr.rgb888p_size, display_size)
                        print("MQTT: Data + CROPPED face published ✅")
                    else:
                        print("MQTT: Data published (no face to crop)")

                # ล้างสถานะหลังจาก 3 วินาที
                if register_status and time.ticks_diff(now, status_clear_time) > 3000:
                    register_status = ""

                # ===== วาด UI บนภาพกล้องโดยตรง =====
                # วาดพื้นหลังแถบบน
                img_display.draw_rectangle(0, 0, DISPLAY_WIDTH, 80, color=(0, 0, 0, 255), fill=True)

                # วาดข้อความ ID (ใหญ่มาก - scale=4)
                id_str = f"ID: {next_id:03d}"
                # วาดเงา
                img_display.draw_string(23, 25, id_str, color=(0, 0, 0, 255), scale=4)
                # วาดข้อความหลัก (สีขาว)
                img_display.draw_string(20, 20, id_str, color=(255, 255, 255, 255), scale=4)

                # วาดจำนวนที่ลงทะเบียน
                reg_str = f"Reg: {fr.registered_count}"
                img_display.draw_string(DISPLAY_WIDTH - 130, 30, reg_str, color=(255, 255, 0, 255), scale=2)

                # วาดแถบสถานะด้านล่าง
                img_display.draw_rectangle(0, 400, DISPLAY_WIDTH, 80, color=(0, 0, 0, 255), fill=True)

                # วาดผลการจดจำ (ถ้ามี)
                if recg_results and len(recg_results) > 0:
                    # แสดงผลการจดจำ
                    recog_text = recg_results[0]
                    img_display.draw_string(20, 420, recog_text, color=(255, 255, 0, 255), scale=2)
                    ###################################################ตรงนี้
                    print(f"แสดงผลการจดจำ: {recog_text}")
                elif register_status:
                    img_display.draw_string(20, 420, register_status, color=(0, 255, 0, 255), scale=2)
                elif target_status:
                    img_display.draw_string(20, 420, target_status, color=(0, 255, 255, 255), scale=2)

                # วาดคำแนะนำการควบคุม
                hint_str = "Short=Next  Long=Save"
                img_display.draw_string(220, 450, hint_str, color=(255, 255, 255, 255), scale=1)

                # ===== วาดกล่อง bounding box รอบใบหน้า =====
                if det_boxes and len(det_boxes) > 0:
                    for i, det in enumerate(det_boxes):
                        if len(det) >= 4:
                            x1, y1, w, h = map(lambda x: int(round(x, 0)), det[:4])
                            x1 = x1 * DISPLAY_WIDTH // fr.rgb888p_size[0]
                            y1 = y1 * DISPLAY_HEIGHT // fr.rgb888p_size[1]
                            w = w * DISPLAY_WIDTH // fr.rgb888p_size[0]
                            h = h * DISPLAY_HEIGHT // fr.rgb888p_size[1]

                            # ตรวจสอบว่าใบหน้านี้รู้จักหรือไม่
                            is_target = False
                            if i < len(recg_results):
                                rec_result = recg_results[i]
                                # ใบหน้าที่รู้จัก = สีเขียว, ไม่รู้จัก = สีแดง
                                is_target = (rec_result != "unknown")

                            # วาดกล่อง (รู้จัก = เขียว, ไม่รู้จัก = แดง)
                            if is_target:
                                img_display.draw_rectangle(x1, y1, w, h, color=(255, 0, 255, 0), thickness=6)
                            else:
                                img_display.draw_rectangle(x1, y1, w, h, color=(255, 255, 0, 0), thickness=4)

                # แสดงภาพพร้อม UI
                Display.show_image(img_display, 0, 0)

            except Exception as e:
                print(f"Error: {e}")
                pass

            gc.collect()

    except KeyboardInterrupt:
        print("\nหยุดโดยผู้ใช้")
    except Exception as e:
        print(f"Error: {e}")
        import sys
        sys.print_exception(e)
    finally:
        # ===== ทำความสะอาด (Cleanup) =====
        print("กำลังทำความสะอาด...")
        try:
            mqtt.disconnect()
        except:
            pass
        try:
            fr.face_det.deinit()
            fr.face_reg.deinit()
        except:
            pass
        try:
            sensor.stop()
            Display.deinit()
        except:
            pass
        print("ทำความสะอาดเรียบร้อย")
