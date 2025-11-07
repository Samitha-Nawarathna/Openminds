document.addEventListener('DOMContentLoaded', function() {
            
  // --- 1. JSON MOCK DATA ---
  const reflectionData = {
      "overview": {
          "avg_notes_per_week": { "count": 28, "change_percentage": 5.2, "title": "Avg. Notes per Week" },
          "avg_mark_improvement": { "score": 3.1, "change_percentage": 1.5, "title": "Avg. Mark Improvement" }, // 3.1 marks improved this week
          "note_exercise_fraction": { "fraction": "2:1", "change_percentage": -4.0, "title": "Note:Exercise Ratio" }
      },
      "weekly_note_activity": [], // Will be generated
      "subject_proficiency": [
          { name: "Calculus", weekly_marks: [1, 2, -1, 3, 0, 4, 1, 2, -1, 3, 0, 4, 1, 2, -1, 3, 0, 4, 1, 2, -1, 3, 0, 4, 1, 2, -1, 3, 0, 4, 1, 2, -1, 3, 0, 4, 1, 2, -1, 3, 0, 4, 1, 2, -1, 3, 0, 4, 1, 2, -1, 3] },
          { name: "Data Structures", weekly_marks: [-1, 0, 3, 2, 4, 1, 0, 1, 0, 3, 2, 4, 1, 0, 1, 0, 3, 2, 4, 1, 0, 1, 0, 3, 2, 4, 1, 0, 1, 0, 3, 2, 4, 1, 0, 1, 0, 3, 2, 4, 1, 0, 1, 0, 3, 2, 4, 1, 0, 1, 0] },
          { name: "Organic Chemistry", weekly_marks: [0, -2, 1, 0, -1, 2, 3, 0, -2, 1, 0, -1, 2, 3, 0, -2, 1, 0, -1, 2, 3, 0, -2, 1, 0, -1, 2, 3, 0, -2, 1, 0, -1, 2, 3, 0, -2, 1, 0, -1, 2, 3, 0, -2, 1, 0, -1, 2, 3, 0, -2, 1] },
          { name: "Linear Algebra", weekly_marks: [2, 1, 3, 0, 2, 1, 3, 0, 2, 1, 3, 0, 2, 1, 3, 0, 2, 1, 3, 0, 2, 1, 3, 0, 2, 1, 3, 0, 2, 1, 3, 0, 2, 1, 3, 0, 2, 1, 3, 0, 2, 1, 3, 0, 2, 1, 3, 0] },
          { name: "European History", weekly_marks: [0, 0, -1, 1, 0, 0, 1, 0, 0, -1, 1, 0, 0, 1, 0, 0, -1, 1, 0, 0, 1, 0, 0, -1, 1, 0, 0, 1, 0, 0, -1, 1, 0, 0, 1, 0, 0, -1, 1, 0, 0, 1, 0, 0, -1, 1, 0, 0, 1, 0, 0, -1] }
      ],
      "top_tags_last_4_weeks": [
          { tag: "#concept-map", w1: 15, w2: 12, w3: 18, w4: 20, drift_w1: 10, drift_w2: 8, drift_w3: 15, drift_w4: 12, total: 65 },
          { tag: "#formula-sheet", w1: 10, w2: 15, w3: 8, w4: 5, drift_w1: 5, drift_w2: 12, drift_w3: 4, drift_w4: 2, total: 38 },
          { tag: "#practice-questions", w1: 25, w2: 20, w3: 22, w4: 18, drift_w1: 20, drift_w2: 18, drift_w3: 19, drift_w4: 15, total: 85 },
          { tag: "#review", w1: 8, w2: 10, w3: 15, w4: 12, drift_w1: 4, drift_w2: 5, drift_w3: 8, drift_w4: 6, total: 45 },
          { tag: "#flashcards", w1: 12, w2: 14, w3: 10, w4: 16, drift_w1: 9, drift_w2: 10, drift_w3: 7, drift_w4: 11, total: 52 },
          { tag: "#summary", w1: 7, w2: 5, w3: 9, w4: 11, drift_w1: 3, drift_w2: 2, drift_w3: 5, drift_w4: 6, total: 32 },
          { tag: "#definition", w1: 5, w2: 6, w3: 7, w4: 8, drift_w1: 2, drift_w2: 3, drift_w3: 4, drift_w4: 5, total: 26 },
          { tag: "#case-study", w1: 3, w2: 4, w3: 5, w4: 6, drift_w1: 1, drift_w2: 2, drift_w3: 3, drift_w4: 4, total: 18 },
          { tag: "#exam-prep", w1: 18, w2: 16, w3: 20, w4: 19, drift_w1: 15, drift_w2: 14, drift_w3: 17, drift_w4: 16, total: 73 },
          { tag: "#project-notes", w1: 4, w2: 3, w3: 6, w4: 5, drift_w1: 2, drift_w2: 1, drift_w3: 4, drift_w4: 3, total: 18 }
      ]
  };
  
  // --- Utility Function: Generate 52 Weeks of Mock Note Activity Data ---
  function generateNoteActivityData(weeks = 52) {
      let data = [];
      let startDate = new Date();
      startDate.setDate(startDate.getDate() - (weeks * 7));
      
      for (let i = 0; i < weeks; i++) {
          let weekStartDate = new Date(startDate);
          weekStartDate.setDate(startDate.getDate() + (i * 7));
          
          data.push({
              date: weekStartDate.getTime(),
              created: Math.floor(20 + (Math.random() * 15)),
              updated: Math.floor(10 + (Math.random() * 10)),
              deleted: Math.floor(2 + (Math.random() * 5))
          });
      }
      reflectionData.weekly_note_activity = data;
  }
  generateNoteActivityData();

  // --- 2. GET DESIGN SYSTEM VARIABLES & SET GLOBAL APEXCHARTS THEME ---
  const rootStyles = getComputedStyle(document.documentElement);
  const colorPrimary = rootStyles.getPropertyValue('--color-primary-accent').trim();
  const colorSecondary = rootStyles.getPropertyValue('--color-secondary-accent').trim();
  const colorTertiary = rootStyles.getPropertyValue('--color-tertiary-accent').trim();
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
  // 📊 IMPLEMENTATION 1: OVERVIEW PANEL (KPI Cards)
  // ========================================
  
  const overviewPanel = document.getElementById('reflection-overview-panel');
  
  const kpiDefinitions = {
      "avg_notes_per_week": { unit: "", format: v => v },
      "avg_mark_improvement": { unit: "pts", format: v => v.toFixed(1) },
      "note_exercise_fraction": { unit: "", format: v => reflectionData.overview.note_exercise_fraction.fraction }
  };
  
  ['avg_notes_per_week', 'avg_mark_improvement', 'note_exercise_fraction'].forEach(key => {
      const data = reflectionData.overview[key];
      const def = kpiDefinitions[key];
      const value = data.count || data.score || data.fraction;
      const isPositive = data.change_percentage >= 0;
      const changeClass = isPositive ? 'positive' : 'negative';
      const sign = isPositive ? '↑' : '↓';
      
      const html = `
          <div class="kpi-card">
              <div class="kpi-title">${data.title}</div>
              <div class="kpi-value">${def.format(value)}${def.unit}</div>
              <div class="kpi-change ${changeClass}">
                  <span>${sign} ${Math.abs(data.change_percentage).toFixed(1)}%</span>
                  <span style="font-weight: 400; color: ${colorTextLight}; margin-left: 0.5rem;">vs. prior week</span>
              </div>
          </div>
      `;
      overviewPanel.insertAdjacentHTML('beforeend', html);
  });


  // ========================================
  // 📈 IMPLEMENTATION 2: WEEKLY NOTE ACTIVITY LINE CHART (52 WEEKS)
  // ========================================
  
  const noteActivityData = reflectionData.weekly_note_activity;

  const noteActivityOptions = {
    series: [
      { name: 'Created', data: noteActivityData.map(d => ({ x: d.date, y: d.created })) },
      { name: 'Updated', data: noteActivityData.map(d => ({ x: d.date, y: d.updated })) },
      { name: 'Deleted', data: noteActivityData.map(d => ({ x: d.date, y: d.deleted })) }
    ],
    chart: { type: 'line', height: 350 },
    colors: [colorPrimary, colorSecondary, '#D32F2F'],
    xaxis: {
      type: 'datetime',
      labels: {
          formatter: (val) => new Date(val).toLocaleDateString('en-US', { month: 'short', year: '2-digit' })
      }
    },
    yaxis: {
      title: { text: 'Note Count', style: { fontWeight: 500 } },
      min: 0
    },
    stroke: { curve: 'smooth', width: 3 },
    legend: { show: true, position: 'top', horizontalAlign: 'right' },
    tooltip: {
      x: { format: 'MMM dd, yyyy' },
      y: { formatter: (val) => val.toLocaleString() }
    }
  };
  
  const noteActivityChart = new ApexCharts(document.querySelector("#note-activity-chart-container"), noteActivityOptions);
  noteActivityChart.render();


  // ========================================
  // 📉 IMPLEMENTATION 3: SUBJECT PROFICIENCY LINE CHART (Dynamic Selector)
  // ========================================
  
  const subjectSelector = document.getElementById('subject-selector');
  let subjectProficiencyChart;

  // Populate Dropdown
  reflectionData.subject_proficiency.forEach((subject, index) => {
      const option = document.createElement('option');
      option.value = index;
      option.textContent = subject.name;
      subjectSelector.appendChild(option);
  });

  function renderProficiencyChart(subjectIndex) {
      const subject = reflectionData.subject_proficiency[subjectIndex];
      const categories = Array.from({ length: 52 }, (_, i) => `W${i + 1}`); 
      
      const proficiencyOptions = {
          series: [{ name: subject.name, data: subject.weekly_marks }],
          chart: { type: 'line', height: 350, id: 'proficiency-chart' },
          colors: [colorTertiary],
          xaxis: {
              categories: categories,
              title: { text: 'Week Number (Last 52)', style: { fontWeight: 500 } },
              labels: { formatter: (val, index) => (index % 5 === 0 ? val : '') }
          },
          yaxis: {
              title: { text: 'Mark Change (+/- points)', style: { fontWeight: 500 } },
              labels: { formatter: (val) => `${val > 0 ? '+' : ''}${val}` }
          },
          markers: { size: 5, strokeWidth: 0 },
          stroke: { curve: 'straight', width: 3 },
          tooltip: {
              y: { formatter: (val) => `${val > 0 ? '+' : ''}${val} points` }
          },
          annotations: {
              yaxis: [{ 
                  y: 0,
                  borderColor: colorTextLight,
                  borderWidth: 1,
                  strokeDashArray: 3,
                  label: {
                      borderColor: colorTextLight,
                      style: { color: '#fff', background: colorTextLight },
                      text: 'Avg Mark Baseline (0)'
                  }
              }]
          }
      };
      
      if (subjectProficiencyChart) {
          subjectProficiencyChart.updateOptions(proficiencyOptions);
      } else {
          subjectProficiencyChart = new ApexCharts(document.querySelector("#subject-proficiency-chart-container"), proficiencyOptions);
          subjectProficiencyChart.render();
      }
  }

  // Initial render
  renderProficiencyChart(0); 

  // Add Event Listener
  subjectSelector.addEventListener('change', (e) => {
      renderProficiencyChart(parseInt(e.target.value));
  });


  // ========================================
  // 🔨 IMPLEMENTATION 4: TOP POPULAR TAGS (Stacked Bar)
  // ========================================
  
  const tagData = reflectionData.top_tags_last_4_weeks.sort((a, b) => b.total - a.total).slice(0, 10);
  const tagNames = tagData.map(d => d.tag);
  
  const popularTagsOptions = {
    series: [
      { name: 'Week 4', data: tagData.map(d => d.w4) },
      { name: 'Week 3', data: tagData.map(d => d.w3) },
      { name: 'Week 2', data: tagData.map(d => d.w2) },
      { name: 'Week 1', data: tagData.map(d => d.w1) }
    ],
    chart: { type: 'bar', height: 350, stacked: true },
    colors: [colorPrimary, colorPrimary + 'C0', colorPrimary + '80', colorPrimary + '40'],
    plotOptions: { bar: { horizontal: true, borderRadius: 4 } },
    dataLabels: { enabled: false },
    xaxis: { categories: tagNames, title: { text: 'Total Activity Count' } },
    legend: { position: 'top', horizontalAlign: 'right' },
    tooltip: { y: { formatter: (val) => `${val} activities` } }
  };

  const popularTagsChart = new ApexCharts(document.querySelector("#popular-tags-chart-container"), popularTagsOptions);
  popularTagsChart.render();
  

  // ========================================
  // 🎯 IMPLEMENTATION 5: ATTENTION DRIFT (Multiple Small Bar Charts) (UPDATED)
  // ========================================
  
  const driftContainer = document.getElementById('attention-drift-container');
  const driftTags = reflectionData.top_tags_last_4_weeks.sort((a, b) => b.total - a.total).slice(0, 10);
  const categories = ['W1', 'W2', 'W3', 'W4'];
  
  driftTags.forEach((tag, index) => {
      // 1. Create a wrapper element for each chart
      const chartWrapper = document.createElement('div');
      chartWrapper.className = 'tag-drift-chart-wrapper';
      
      const title = document.createElement('div');
      title.className = 'tag-drift-chart-title';
      title.textContent = tag.tag;
      chartWrapper.appendChild(title);
      
      const chartId = `drift-chart-${index}`;
      const chartDiv = document.createElement('div');
      chartDiv.id = chartId;
      chartWrapper.appendChild(chartDiv);
      
      driftContainer.appendChild(chartWrapper);
      
      // 2. Prepare the data for the current tag
      const seriesData = [tag.drift_w1, tag.drift_w2, tag.drift_w3, tag.drift_w4];
      const maxDriftValue = Math.max(...seriesData, 1); // Get max value to set Y-axis height
      
      // 3. Configure and render the chart
      const driftChartOptions = {
          series: [{ name: 'Drift Score', data: seriesData }],
          chart: { 
              type: 'bar', 
              height: 120, // Small height for the minichart
              sparkline: { enabled: true } // Makes it a clean minichart
          },
          colors: [colorSecondary],
          plotOptions: { bar: { columnWidth: '60%', borderRadius: 4 } },
          xaxis: { categories: categories, labels: { show: true } }, // Show X-axis labels (W1, W2, etc.)
          yaxis: { 
              show: false, // Hide Y-axis numbers
              max: maxDriftValue * 1.2 // Give a little padding above the max value
          },
          grid: { show: false },
          tooltip: {
              enabled: true,
              y: { formatter: (val) => `${val} Drift Score` }
          }
      };
      
      const driftChart = new ApexCharts(document.querySelector(`#${chartId}`), driftChartOptions);
      driftChart.render();
  });


});
