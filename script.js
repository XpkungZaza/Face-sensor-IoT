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
