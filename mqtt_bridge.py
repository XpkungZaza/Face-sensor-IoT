import paho.mqtt.client as mqtt
import requests
import json

# --- ตั้งค่าพิกัด ---
MQTT_BROKER = "178.128.49.254"
MQTT_PORT = 1883
TOPIC = "rcn/#"  # ดักฟังทุกอย่างที่ขึ้นต้นด้วย rcn/
# URL ของไฟล์ PHP บน Hostatom (ถ้ายันเข้าไม่ได้ ให้ใช้ localhost เทสก่อนถ้ามี XAMPP)
PHP_URL = "https://yourdomain.com/update_status.php" 

def on_connect(client, userdata, flags, rc):
    print("เชื่อมต่อ MQTT Broker สำเร็จ!")
    client.subscribe(TOPIC)

def on_message(client, userdata, msg):
    payload_raw = msg.payload.decode()
    print(f"ได้รับข้อมูลจาก {msg.topic}: {payload_raw}")
    
    # --- สมมติข้อมูลที่ส่งมาคือรหัสนักเรียนตรงๆ ---
    data_to_send = {
        'student_id': payload_raw, 
        'status': 'On Bus' # หรือจะเขียน Logic แยกตาม Topic ก็ได้
    }

    try:
        r = requests.post(PHP_URL, data=data_to_send)
        print(f"ส่งไป Hostatom สำเร็จ: {r.text}")
    except Exception as e:
        print(f"เกิดข้อผิดพลาดในการส่ง: {e}")

client = mqtt.Client()
client.on_connect = on_connect
client.on_message = on_message

print("กำลังรอรับข้อมูลจากบอร์ด K230...")
client.connect(MQTT_BROKER, MQTT_PORT, 60)
client.loop_forever() # รันค้างไว้ถาวร!