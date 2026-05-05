import paho.mqtt.client as mqtt
import json

client = mqtt.Client("Test_Shooter")
client.connect("178.128.49.254", 1883)

# ยิงข้อมูลจำลองรหัส 002 (Master Prite)
data = {"recognition": "ID:002 0.99"} 
client.publish("RCN/ID001/DATA", json.dumps(data))

print("🎯 ยิงข้อมูลทดสอบไปที่ RCN/ID001/DATA แล้ว!")
client.disconnect()