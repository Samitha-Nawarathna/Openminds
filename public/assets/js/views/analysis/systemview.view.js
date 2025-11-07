document.addEventListener('DOMContentLoaded', function() {
            
  // --- 1. JSON MOCK DATA ---
  const adminData = {
      "profile_status": {
          "active": 4567,
          "banned": 124
      },
      "subjects": [
          { name: "Calculus", growth_rate: 4.5, avg_score: 85.2, exercise_count: 512, expert_count: 15 },
          { name: "Data Structures", growth_rate: 1.2, avg_score: 92.1, exercise_count: 340, expert_count: 8 },
          { name: "Organic Chemistry", growth_rate: -2.3, avg_score: 78.5, exercise_count: 670, expert_count: 22 },
          { name: "Linear Algebra", growth_rate: 0.8, avg_score: 88.9, exercise_count: 410, expert_count: 11 },
          { name: "European History", growth_rate: 5.9, avg_score: 75.0, exercise_count: 280, expert_count: 4 }
      ]
  };

  // --- 2. GET DESIGN SYSTEM VARIABLES & SET GLOBAL APEXCHARTS THEME ---
  const rootStyles = getComputedStyle(document.documentElement);
  const colorPrimary = rootStyles.getPropertyValue('--color-primary-accent').trim();
  const colorRed = rootStyles.getPropertyValue('--color-red').trim();
  const colorTextLight = rootStyles.getPropertyValue('--color-text-light').trim();
  const fontFamily = rootStyles.getPropertyValue('--font-family-primary').trim();
  const colorBorder = rootStyles.getPropertyValue('--color-border').trim();

  Apex.theme = {
    mode: 'light',
    chart: { fontFamily: fontFamily, foreColor: colorTextLight, toolbar: { show: false } },
    grid: { borderColor: colorBorder },
    xaxis: { axisBorder: { color: colorBorder }, axisTicks: { color: colorBorder } },
    yaxis: { lines: { show: true } },
    dataLabels: { style: { fontFamily: fontFamily } },
    tooltip: { theme: 'light', style: { fontFamily: fontFamily } }
  };
  
  // ========================================
  // 📊 IMPLEMENTATION 2: PROFILE STATUS (Bar Chart)
  // ========================================
  
  const profileStatusData = adminData.profile_status;

  const profileStatusOptions = {
    series: [{ 
        name: 'Count', 
        data: [profileStatusData.active, profileStatusData.banned] 
    }],
    chart: { 
        type: 'bar', 
        height: 300 
    },
    colors: [colorPrimary, colorRed],
    plotOptions: {
      bar: {
        horizontal: false,
        columnWidth: '50%',
        endingShape: 'rounded'
      }
    },
    dataLabels: { enabled: true },
    xaxis: {
      categories: ['Active Profiles', 'Banned Profiles'],
      labels: { show: true }
    },
    yaxis: {
      title: { text: 'Profile Count', style: { fontWeight: 500 } },
      labels: { formatter: (val) => val.toLocaleString() }
    },
    legend: { show: false }
  };
  
  const profileStatusChart = new ApexCharts(document.querySelector("#profile-status-chart-container"), profileStatusOptions);
  profileStatusChart.render();


  // ========================================
  // 📰 IMPLEMENTATION 3: SUBJECT MANAGEMENT (Table)
  // ========================================
  
  const tableBody = document.getElementById('subject-management-table-body');
  
  adminData.subjects.forEach(subject => {
      const row = document.createElement('tr');
      
      // --- 1. Subject Name ---
      const nameCell = document.createElement('td');
      nameCell.textContent = subject.name;
      nameCell.style.fontWeight = '600';
      row.appendChild(nameCell);
      
      // --- 2. Growth Rate (Pill) ---
      const rateCell = document.createElement('td');
      const pill = document.createElement('span');
      const isPositive = subject.growth_rate >= 0;
      
      pill.className = `growth-rate ${isPositive ? 'growth-positive' : 'growth-negative'}`;
      pill.textContent = `${isPositive ? '▲' : '▼'} ${Math.abs(subject.growth_rate).toFixed(1)}%`;
      rateCell.appendChild(pill);
      row.appendChild(rateCell);
      
      // --- 3. Avg. Exercise Score ---
      const scoreCell = document.createElement('td');
      scoreCell.textContent = subject.avg_score.toFixed(1);
      row.appendChild(scoreCell);
      
      // --- 4. Exercise Count ---
      const exerciseCell = document.createElement('td');
      exerciseCell.textContent = subject.exercise_count.toLocaleString();
      row.appendChild(exerciseCell);
      
      // --- 5. Expert Count ---
      const expertCell = document.createElement('td');
      expertCell.textContent = subject.expert_count.toLocaleString();
      expertCell.style.fontWeight = '600';
      row.appendChild(expertCell);
      
      tableBody.appendChild(row);
  });
});
