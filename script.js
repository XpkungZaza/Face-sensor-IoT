fetch('https://yourdomain.com/get_students.php')
  .then(response => response.json())
  .then(data => {
    console.log(data); // ข้อมูลเด็ก 10 คนที่นายเพิ่งเพิ่มไปจะโผล่ตรงนี้!
    // เอาไปวนลูปโชว์ในตารางได้เลย
  });
// -------------------------------------------------------------
// Page: index.html (Main Dashboard / Live Log)
// -------------------------------------------------------------
async function initDashboard() {
  const logContainer = document.getElementById('live-log-container');
  if (!logContainer) return;
  
  const students = await fetchStudents();
  
  // Filter only active students for the mock live log
  const activeStudents = students.filter(s => s.status === 'Active');
  
  logContainer.innerHTML = '';
  
  if (activeStudents.length === 0) {
    logContainer.innerHTML = '<p>No currently active students.</p>';
    return;
  }

  // Simulate recent check-ins by reversing
  activeStudents.reverse().forEach(student => {
    const logItem = document.createElement('div');
    logItem.className = 'log-item';
    
    // Check-in time mock (current time minus some random minutes)
    const mockTime = new Date();
    mockTime.setMinutes(mockTime.getMinutes() - Math.floor(Math.random() * 60));
    const timeString = mockTime.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

    logItem.innerHTML = `
      <img src="${student.image_url}" alt="${student.name}">
      <div style="flex: 1;">
        <h4 style="margin-bottom: 0.25rem;">${student.name}</h4>
        <p style="font-size: 0.875rem; color: var(--text-muted);">ID: ${student.id} | ${student.grade}</p>
      </div>
      <div style="text-align: right; color: var(--success); font-weight: 600; font-size: 0.875rem;">
        ${timeString}
      </div>
    `;
    logContainer.appendChild(logItem);
  });
}

// -------------------------------------------------------------
// Page: id-list.html (All Students View)
// -------------------------------------------------------------
async function initStudentGrid() {
  const gridContainer = document.getElementById('student-grid');
  if (!gridContainer) return;

  const students = await fetchStudents();
  gridContainer.innerHTML = '';

  students.forEach(student => {
    const card = document.createElement('div');
    card.className = 'card student-card';
    card.innerHTML = `
      <img src="${student.image_url}" alt="${student.name}">
      <h3>${student.name}</h3>
      <p>ID: ${student.id}<br>${student.grade}</p>
      <a href="student-detail.html?id=${student.id}" class="btn">View Details</a>
    `;
    gridContainer.appendChild(card);
  });
}

// -------------------------------------------------------------
// Page: student-detail.html (Dynamic Student Profile)
// -------------------------------------------------------------
async function initStudentDetail() {
  const detailContainer = document.getElementById('student-detail-container');
  if (!detailContainer) return;

  // Get ID from URL
  const params = new URLSearchParams(window.location.search);
  const studentId = params.get('id');

  if (!studentId) {
    detailContainer.innerHTML = '<div class="card"><p>No student ID provided.</p></div>';
    return;
  }

  const students = await fetchStudents();
  const student = students.find(s => s.id === studentId);

  if (!student) {
    detailContainer.innerHTML = '<div class="card"><p>Student not found.</p></div>';
    return;
  }

  // Render the student dynamic data
  detailContainer.innerHTML = `
    <div class="card">
      <div class="profile-header">
        <img src="${student.image_url}" alt="${student.name}">
        <div class="profile-info">
          <h1>${student.name}</h1>
          <span class="badge ${student.status.toLowerCase()}">${student.status}</span>
        </div>
      </div>
      
      <div class="info-grid">
        <div class="info-item">
          <div class="info-label">Student ID</div>
          <div class="info-value">${student.id}</div>
        </div>
        <div class="info-item">
          <div class="info-label">Grade / Class</div>
          <div class="info-value">${student.grade}</div>
        </div>
        <div class="info-item">
          <div class="info-label">Check-in Status</div>
          <div class="info-value" style="color: ${student.status === 'Active' ? 'var(--success)' : 'var(--danger)'};">
            ${student.status === 'Active' ? 'Checked In' : 'Not Checked In'}
          </div>
        </div>
        <div class="info-item">
          <div class="info-label">Last Updated</div>
          <div class="info-value">${new Date().toLocaleDateString()}</div>
        </div>
      </div>
    </div>
  `;
}

// -------------------------------------------------------------
// Page: user-info.html (Teacher Dashboard)
// -------------------------------------------------------------
async function initAdminDashboard() {
  const totalStats = document.getElementById('stat-total');
  const presentStats = document.getElementById('stat-present');
  const absentStats = document.getElementById('stat-absent');
  const tableBody = document.getElementById('active-students-table-body');
  
  if (!totalStats || !presentStats || !absentStats || !tableBody) return;

  const students = await fetchStudents();
  
  // Calculate stats
  const total = students.length;
  const activeStudents = students.filter(s => s.status === 'Active');
  const present = activeStudents.length;
  const absent = total - present;

  // Update DOM stats
  totalStats.textContent = total;
  presentStats.textContent = present;
  absentStats.textContent = absent;

  // Populate table with Active students only
  tableBody.innerHTML = '';
  if (activeStudents.length === 0) {
    tableBody.innerHTML = '<tr><td colspan="4" style="text-align: center;">No active students currently.</td></tr>';
    return;
  }

  activeStudents.forEach(student => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>
        <img src="${student.image_url}" alt="${student.name}">
        ${student.name}
      </td>
      <td>${student.id}</td>
      <td>${student.grade}</td>
      <td><span class="badge active">Active</span></td>
    `;
    tableBody.appendChild(tr);
  });
}

// -------------------------------------------------------------
// Webcam Access Logic
// -------------------------------------------------------------
async function initWebcam() {
  const videoFeed = document.getElementById('webcam-feed');
  if (!videoFeed) return;

  try {
    const stream = await navigator.mediaDevices.getUserMedia({ video: true });
    videoFeed.srcObject = stream;
  } catch (error) {
    console.error("Error accessing webcam:", error);
    // Fallback UI or display an alert if camera access fails
    videoFeed.parentElement.innerHTML += `<p style="color:red;font-size:0.875rem;">Error accessing webcam. Please allow permissions.</p>`;
  }
}

// Initialize appropriate scripts based on the loaded page
document.addEventListener("DOMContentLoaded", () => {
  initDashboard();
  initStudentGrid();
  initStudentDetail();
  initAdminDashboard();
  initWebcam();
});
