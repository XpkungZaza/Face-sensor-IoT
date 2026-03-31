const studentDB = [ //wait for data
    { id: "66001", name: "ด.ช. การิน ", status: "ขึ้นรถแล้ว", statusClass: "success" },
    { id: "66002", name: "ด.ญ. สมหญิง ", status: "ยังไม่มา", statusClass: "pending" },
    { id: "66003", name: "ด.ช. สมฮง ", status: "ขึ้นรถแล้ว", statusClass: "success" }
];

const container = document.getElementById('student-list-container');

studentDB.forEach((student) =>  {
    const cardHTML = `
        <div class="student-card">
            <div class="card-info">
                <h3>${student.name}</h3>
                <p>รหัส: ${student.id}</p>
            </div>
            <div class="card-status">
                <span class="status-badge ${student.statusClass}">${student.status}</span>
            </div>
        </div>
     `; 
        container.innerHTML += cardHTML;
    })
    const loginBtn = document.getElementById('loginBtn');
const passwordInput = document.getElementById('adminPassword');

loginBtn.addEventListener('click', () => {
    // สมมติว่ารหัสผ่านคือ 1234 (เดี๋ยวอนาคตค่อยไปเช็คกับ Firebase)
    if (passwordInput.value === "1234") {
        alert("เข้าสู่ระบบสำเร็จ!");
        window.location.href = "index.html"; // คำสั่งย้ายหน้าเว็บไป index.html
    } else {
        alert("รหัสผ่านไม่ถูกต้องนะเพื่อน!");
    }
});
