import {ROOT} from '../../core/config.js';

document.addEventListener('DOMContentLoaded', function() {

  // --- 1. API CONFIGURATION ---
  const API_URL = ROOT + 'analysis/api/systemview_data'; 
  
  // --- 2. GET DESIGN SYSTEM VARIABLES & SET GLOBAL APEXCHARTS THEME ---
  // ... (rest of the theme setup remains unchanged) ...
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
  // 📊 RENDERING FUNCTION 1: PROFILE STATUS CHART
  // ========================================
  function renderProfileStatusChart(profileStatusData) {
      // ... (function body remains unchanged) ...
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
  }


  // ========================================
  // 📰 RENDERING FUNCTION 2: SUBJECT MANAGEMENT TABLE
  // ========================================
  function renderSubjectManagementTable(subjectsData) {
      // ... (function body remains unchanged) ...
      const tableBody = document.getElementById('subject-management-table-body');
      
      // Clear any previous mock data or loading placeholders
      tableBody.innerHTML = ''; 

      subjectsData.forEach(subject => {
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
  }

  // ========================================
  // ⭐ NEW RENDERING FUNCTION 3: OVERVIEW KPIS
  // ========================================
  function renderOverviewKPIs(kpis) {
      // Update Pending Expert Requests
      document.getElementById('kpi-expert-requests').textContent = kpis.pending_expert_requests.toLocaleString();

      // Update Total Active Profiles
      // We reuse the active count from the overview_kpis structure
      document.getElementById('kpi-active-profiles').textContent = kpis.total_active_profiles.toLocaleString();

      // Update System Health Score
      document.getElementById('kpi-system-health').textContent = `${kpis.system_health_score.toFixed(1)}%`;
  }

  // ========================================
  // ⚙️ DATA FETCHING LOGIC (UPDATED)
  // ========================================

  async function loadAdminData() {
      try {
          const response = await fetch(API_URL);

          if (!response.ok) {
              throw new Error(`HTTP error! status: ${response.status}`);
          }

          const adminData = await response.json();
          
          // 1. Render the new KPI overview cards
          renderOverviewKPIs(adminData.overview_kpis);

          // 2. Render the Profile Status Bar Chart
          renderProfileStatusChart(adminData.profile_status);
          
          // 3. Render the Subject Management Table
          renderSubjectManagementTable(adminData.subjects);

      } catch (error) {
          console.error('Error fetching admin data:', error);
          
          const errorContainer = document.querySelector('#main-dashboard-container') || document.body;
          errorContainer.innerHTML = '<div style="color: red; padding: 20px; border: 1px solid red; margin: 20px;">' +
                                     '<h3>⚠️ Error Loading System Data</h3>' +
                                     '<p>Could not load administrative data. Check the API endpoint and server connection.</p>' +
                                     '</div>';
      }
  }

  // Start loading data when the DOM is ready
  loadAdminData();
});