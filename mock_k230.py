import paho.mqtt.client as mqtt

# ตั้งค่าให้ตรงกับ Bridge ของนายเป๊ะๆ
BROKER = "178.128.49.254"
PORT = 1883
TOPIC = "rcn/k230_1"  # ใช้ชื่ออะไรก็ได้ที่ขึ้นต้นด้วย rcn/ เพราะ bridge ดักฟัง rcn/# ไว้

client = mqtt.Client("K230_Simulator")
client.connect(BROKER, PORT)

# 🎯 ใส่รหัสนักเรียน ป.1 ที่นายเพิ่งเอาเข้า Database ไป!
test_student_id = "69001" 

# ยิงรหัสไปเพียวๆ เลย
client.publish(TOPIC, test_student_id)
print(f"📡 ยิงข้อมูล: '{test_student_id}' ไปที่ Topic: {TOPIC} แล้ว!")

client.disconnect()