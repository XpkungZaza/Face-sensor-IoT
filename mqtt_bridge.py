import paho.mqtt.client as mqtt
import requests
import json
import base64

# --- Configuration ---
MQTT_BROKER = "178.128.49.254"
MQTT_PORT = 1883
TOPIC = "RCN/#" 
# แก้ไข Path ให้ตรงกับ Folder โปรเจกต์ของมาสเตอร์
PHP_URL = "http://localhost/Face-sensor-IoT/update_status.php" 
SAVE_PATH = "C:/xampp/htdocs/Face-sensor-IoT/assets/last_scan.jpg"

def send_to_php(student_id):
    """ส่งข้อมูลรหัสนักเรียนไปอัปเดตสถานะใน MySQL ผ่าน PHP"""
    data_to_send = {
        'student_id': student_id, 
        'status': 'On Bus'
    }
    try:
        r = requests.post(PHP_URL, data=data_to_send)
        print(f"📡 ส่ง ID: {student_id} ไป PHP สำเร็จ: {r.text}")
    except Exception as e:
        print(f"❌ เกิดข้อผิดพลาดในการส่งไป PHP: {e}")

def on_connect(client, userdata, flags, rc):
    print("✅ เชื่อมต่อ MQTT Broker สำเร็จ!")
    client.subscribe(TOPIC)

def on_message(client, userdata, msg):
    print(f"📩 ได้รับจาก Topic: {msg.topic}")

    # ดึงค่า payload ออกมาก่อนครั้งเดียวเพื่อใช้ทั้งภาพและ DATA
    try:
        payload = json.loads(msg.payload.decode())
    except Exception as e:
        print("❌ แปลงข้อมูล JSON ไม่ได้:", e)
        return

    # ---------------- 📸 ส่วนจัดการรูปภาพ (IMAGE) ----------------
    if "image" in msg.topic:
        try:
            # เช็คให้ชัวร์ว่า Key ชื่อ 'image' ตรงกับที่บอร์ดส่งมา
            image_base64 = payload['image'] 
            
            with open(SAVE_PATH, "wb") as f:
                f.write(base64.b64decode(image_base64))
            print("📸 บันทึกภาพสำเร็จ!")
        except Exception as e:
            print(f"❌ บันทึกภาพล้มเหลว: {e}")

    # ---------------- 🆔 ส่วนจัดการข้อมูล (DATA) ----------------
    elif "DATA" in msg.topic:
        try:
            # รูปแบบข้อมูลที่ได้รับคือ "ID:002 0.89"
            recog_result = payload['recognition']
            
            # แยกเอาเฉพาะเลข ID (002)
            student_id = recog_result.split(':')[1].split(' ')[0]
            print(f"🔍 ตรวจพบนักเรียน ID: {student_id}")

            # ส่งต่อให้ PHP จัดการลง Database
            send_to_php(student_id)
        except Exception as e:
            print("❌ ประมวลผลข้อมูล DATA ล้มเหลว:", e)
# --- Main Runtime ---
client = mqtt.Client()
client.on_connect = on_connect
client.on_message = on_message

print("🚀 กำลังพยายามเชื่อมต่อ Broker...")
try:
    client.connect(MQTT_BROKER, MQTT_PORT, 60)
    # ไม่ใช้ loop_forever() แบบเงียบๆ แต่ใช้แบบเห็นสถานะ
    print("📡 Bridge กำลังดักฟังข้อมูล... (กด Ctrl+C เพื่อหยุด)")
    client.loop_forever() 
except Exception as e:
    print(f"❌ เชื่อมต่อล้มเหลว: {e}")