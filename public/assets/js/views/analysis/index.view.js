import {ROOT} from '../../core/config.js';

document.addEventListener('DOMContentLoaded', function() {
            
    // --- 1. JSON MOCK DATA (Updated with Subject Toggle Data) ---

    async function getAnalyticsData() {
      try {
        const response = await fetch(ROOT + 'analysis/api/dashboard_data');
        if (!response.ok) throw new Error('Network response was not ok');
    
        const data = await response.json();
        const analyticsData = Array.isArray(data) ? data : [data];
    
        console.log("returned data", analyticsData); // now works
        return analyticsData;
      } catch (error) {
        console.error('Fetch error:', error);
      }
    }

    // async function initData()
    // {
    //   return await getAnalyticsData().;
    // }
    
    const analyticsData = getAnalyticsData().then(analyticsData => 
    {
        console.log("inside fetch call", analyticsData); 

    // --- 2. GET DESIGN SYSTEM VARIABLES ---
    const rootStyles = getComputedStyle(document.documentElement);
    const colorPrimary = rootStyles.getPropertyValue('--color-primary-accent').trim();
    const colorSecondary = rootStyles.getPropertyValue('--color-secondary-accent').trim();
    const colorTertiary = rootStyles.getPropertyValue('--color-tertiary-accent').trim();
    const colorTextLight = rootStyles.getPropertyValue('--color-text-light').trim();
    const fontFamily = rootStyles.getPropertyValue('--font-family-primary').trim();
    const colorBorder = rootStyles.getPropertyValue('--color-border').trim();

    // --- 3. SET GLOBAL APEXCHARTS THEME ---
    Apex.theme = {
      mode: 'light',
      chart: {
        fontFamily: fontFamily,
        foreColor: colorTextLight, 
        toolbar: { show: false }
      },
      grid: { borderColor: colorBorder },
      xaxis: {
        axisBorder: { color: colorBorder },
        axisTicks: { color: colorBorder }
      },
      yaxis: { lines: { show: true } },
      dataLabels: {
        style: { fontFamily: fontFamily }
      },
      tooltip: {
        theme: 'light',
        style: { fontFamily: fontFamily }
      }
    };
    
    // ========================================
    // 📊 IMPLEMENTATION 1: OVERVIEW PANEL (KPI Cards)
    // ========================================
    
    const overviewPanel = document.getElementById('overview-panel');
    
    const kpiDefinitions = {
        "notes_created": { title: "Notes Created (Last Week)", unit: "", format: v => v },
        "average_exercise_score": { title: "Avg. Exercise Score", unit: "%", format: v => v.toFixed(1) },
        "all_votes": { title: "Total Votes", unit: "", format: v => v},
        "learning_consistency": { title: "Learning Consistency", unit: "", format: v => analyticsData[0].overview.learning_consistency.fraction }
    };
    
    Object.keys(kpiDefinitions).forEach(key => {
        console.log(analyticsData[0].overview);
        const data = analyticsData[0].overview[key];
        const def = kpiDefinitions[key];
        const isPositive = data.change_percentage >= 0;
        const changeClass = isPositive ? 'positive' : 'negative';
        const sign = isPositive ? '↑' : '↓';
        
        const html = `
            <div class="kpi-card">
                <div class="kpi-title">${def.title}</div>
                <div class="kpi-value">${def.format(data.count || data.score || data.fraction)}${def.unit}</div>
                <div class="kpi-change ${changeClass}">
                    <span>${sign} ${Math.abs(data.change_percentage).toFixed(1)}%</span>
                    <span style="font-weight: 400; color: ${colorTextLight}; margin-left: 0.5rem;">vs. prior period</span>
                </div>
            </div>
        `;
        overviewPanel.insertAdjacentHTML('beforeend', html);
    });


    // ========================================
    // 📈 IMPLEMENTATION 2: WEEKLY TRENDS LINE CHART (52 WEEKS)
    // ========================================
    
    const weeklyTrendsData = analyticsData[0].weekly_trends;
    let weeklyTrendsChart;

    const trendsChartOptions = {
      series: [
        { name: 'Notes Created', data: weeklyTrendsData.map(d => ({ x: d.week_start_date, y: d.notes_created })) },
        { name: 'Questions Asked', data: weeklyTrendsData.map(d => ({ x: d.week_start_date, y: d.questions_asked })) },
        { name: 'Exercises Attempted', data: weeklyTrendsData.map(d => ({ x: d.week_start_date, y: d.exercises_attempted })) }
      ],
      chart: {
        type: 'line',
        height: 350,
        id: 'weekly-trends',
        toolbar: { show: false }
      },
      colors: [colorPrimary, colorSecondary, colorTertiary],
      xaxis: {
        type: 'datetime',
        tooltip: { enabled: false },
        labels: {
            formatter: (val) => {
                return new Date(val).toLocaleDateString('en-US', { month: 'short', year: '2-digit' });
            }
        }
      },
      yaxis: {
        title: { text: 'Count', style: { fontWeight: 500 } },
        min: 0,
        tickAmount: 5
      },
      stroke: { curve: 'smooth', width: 3 },
      dataLabels: { enabled: false },
      legend: { show: false },
      tooltip: {
        x: { format: 'MMM dd, yyyy' },
        y: { formatter: (val) => val.toLocaleString() }
      }
    };
    
    weeklyTrendsChart = new ApexCharts(document.querySelector("#weekly-trends-chart-container"), trendsChartOptions);
    weeklyTrendsChart.render().then(() => {
        weeklyTrendsChart.toggleSeries('Questions Asked');
        weeklyTrendsChart.toggleSeries('Exercises Attempted');
    });
    
    // Add Toggle Functionality for Weekly Trends
    const trendToggleButtons = document.querySelectorAll("#trend-chart-toggles .toggle-button");
    
    trendToggleButtons.forEach(button => {
        button.addEventListener('click', () => {
            const seriesName = button.textContent;
            
            trendToggleButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            
            const seriesNames = ["Notes Created", "Questions Asked", "Exercises Attempted"];

            seriesNames.forEach(name => {
                if (name === seriesName) {
                    weeklyTrendsChart.showSeries(name);
                } else {
                    weeklyTrendsChart.hideSeries(name);
                }
            });
        });
    });

    // ========================================
    // 🎯 IMPLEMENTATION 3: 52-WEEK ACTIVITY MATRIX (HEATMAP) (NEW)
    // ========================================

    // We need a color range for the heatmap that is based on the primary color
  //   const heatmapColors = [
  //     { from: 0, to: 0, color: '#F0F0F0', name: 'No Activity' }, // Lightest grey for 0 activity
  //     { from: 1, to: 5, color: '#D0E9E5', name: 'Low' },
  //     { from: 6, to: 15, color: '#80D4C8', name: 'Medium' },
  //     { from: 16, to: 25, color: '#40BFB1', name: 'High' },
  //     { from: 26, to: 30, color: colorPrimary, name: 'Very High' }
  // ];

  // const heatmapOptions = {
  //     series: heatmapActivityData,
  //     chart: {
  //         type: 'heatmap',
  //         height: 180,
  //         toolbar: { show: false }
  //     },
  //     dataLabels: { enabled: false },
  //     colors: [colorPrimary],
  //     plotOptions: {
  //         heatmap: {
  //             radius: 2, // Slightly rounded corners
  //             enableShades: false,
  //             colorScale: {
  //                 ranges: heatmapColors
  //             }
  //         }
  //     },
  //     xaxis: {
  //         categories: Array.from({ length: 52 }, (_, i) => `W${i + 1}`),
  //         labels: {
  //             show: true,
  //             rotate: 0,
  //             formatter: (val, index) => (index % 5 === 0 ? val : ''), // Show every 5th week number
  //             style: { colors: colorTextLight }
  //         },
  //         tooltip: { enabled: false }
  //     },
  //     yaxis: {
  //         labels: { style: { colors: colorTextLight } }
  //     },
  //     grid: { show: false },
  //     tooltip: {
  //         y: { formatter: (val) => `${val} activities` }
  //     },
  //     legend: {
  //         show: true,
  //         position: 'bottom',
  //         markers: { radius: 12 },
  //         itemMargin: { horizontal: 10 }
  //     }
  // };
  
  // const activityMatrix = new ApexCharts(document.querySelector("#activity-matrix-container"), heatmapOptions);
  // activityMatrix.render();

    // ========================================
    // 🔨 IMPLEMENTATION 4: TOP SUBJECTS BAR CHART (Toggleable)
    // ========================================
    
    let subjectsChart;

    function renderSubjectsChart(viewKey) {
        const subjectsRawData = analyticsData[0].top_subjects[viewKey].slice(0, 10).sort((a, b) => a.average_score - b.average_score); 
        const subjectNames = subjectsRawData.map(s => s.subject_name);
        const subjectScores = subjectsRawData.map(s => s.average_score.toFixed(1));

        const subjectsChartOptions = {
          series: [{ name: 'Avg. Score', data: subjectScores }],
          chart: { 
            type: 'bar', 
            height: 350,
            id: 'top-subjects-chart',
            toolbar: { show: false }
          },
          colors: [colorPrimary],
          plotOptions: {
            bar: {
              horizontal: true,
              borderRadius: 4,
              dataLabels: { position: 'top' }
            }
          },
          dataLabels: {
            enabled: true,
            formatter: (val) => `${val}%`,
            offsetX: 40, 
            style: {
                colors: ['#fff'], 
                fontSize: '12px'
            }
          },
          xaxis: {
            categories: subjectNames,
            max: 100,
            title: { text: 'Average Score (%)', style: { fontWeight: 500 } }
          },
          yaxis: {
            labels: { style: { fontWeight: 500, color: colorTextLight } }
          },
          tooltip: { y: { formatter: (val) => `${val}%` } },
          grid: { xaxis: { lines: { show: true } } }
        };

        if (subjectsChart) {
            subjectsChart.updateOptions(subjectsChartOptions);
        } else {
            subjectsChart = new ApexCharts(document.querySelector("#top-subjects-chart-container"), subjectsChartOptions);
            subjectsChart.render();
        }
    }

    // Initial render for 'All Time'
    renderSubjectsChart('all_time');

    // Add Toggle Functionality for Top Subjects
    const subjectToggleButtons = document.querySelectorAll("#subject-chart-toggles .toggle-button");
    
    subjectToggleButtons.forEach(button => {
        button.addEventListener('click', () => {
            subjectToggleButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            
            const viewKey = button.getAttribute('data-subject-view');
            renderSubjectsChart(viewKey);
        });
    });


        // ========================================
    // 🎯 IMPLEMENTATION 5: TOP TAGS BAR CHART (Toggleable)
    // ========================================

    let tagsChart;
    
    function renderTagsChart(viewKey) {
        const tagsRawData = analyticsData[0].top_tags[viewKey].slice(0, 10).sort((a, b) => a.activity_count - b.activity_count); 
        const tagNames = tagsRawData.map(t => t.tag_name);
        const tagCounts = tagsRawData.map(t => t.activity_count);

        const tagsChartOptions = {
          series: [{ name: 'Activity Count', data: tagCounts }],
          chart: {
            type: 'bar', 
            height: 350,
            id: 'top-tags-chart',
            toolbar: { show: false }
          },
          colors: [colorSecondary],
          plotOptions: {
            bar: {
              horizontal: true,
              borderRadius: 4,
              dataLabels: { position: 'top' }
            }
          },
          dataLabels: {
            enabled: true,
            formatter: (val) => val,
            offsetX: 40,
            style: {
                colors: ['#fff'],
                fontSize: '12px'
            }
          },
          xaxis: {
            categories: tagNames,
            title: { text: 'Activity Count', style: { fontWeight: 500 } }
          },
          yaxis: {
            labels: { style: { fontWeight: 500, color: colorTextLight } }
          },
          tooltip: { y: { formatter: (val) => `${val} activities` } },
          grid: { xaxis: { lines: { show: true } } }
        };
        
        if (tagsChart) {
            tagsChart.updateOptions(tagsChartOptions);
        } else {
            tagsChart = new ApexCharts(document.querySelector("#top-tags-chart-container"), tagsChartOptions);
            tagsChart.render();
        }
    }
    
    // Initial render for 'All Time'
    renderTagsChart('all_time');
    
    // Add Toggle Functionality for Top Tags
    const tagToggleButtons = document.querySelectorAll("#tag-chart-toggles .toggle-button");
    
    tagToggleButtons.forEach(button => {
        button.addEventListener('click', () => {
            tagToggleButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            
            const viewKey = button.getAttribute('data-tag-view');
            renderTagsChart(viewKey);
        });
    });
        


    }
    );
    
    
    // console.log("analyticsData", analyticsData); // undefined here
    // const analyticsData = {
    //   "overview": {
    //     "notes_created": { "count": 55, "change_percentage": 15.7 },
    //     "average_exercise_score": { "score": 82.5, "change_percentage": -2.1 },
    //     "all_votes": { "count": 450, "change_percentage": 25.0 },
    //     "learning_consistency": { "fraction": "5/7", "change_percentage": 7.5 }
    //   },
    //   "weekly_trends": [], 
    //   "top_subjects": {
    //     "all_time": [
    //         { "subject_name": "Calculus", "average_score": 91.2 },
    //         { "subject_name": "Data Structures", "average_score": 88.5 },
    //         { "subject_name": "History of Art", "average_score": 85.0 },
    //         { "subject_name": "Organic Chemistry", "average_score": 82.1 },
    //         { "subject_name": "Macroeconomics", "average_score": 79.5 },
    //         { "subject_name": "Linguistics", "average_score": 77.0 },
    //         { "subject_name": "Linear Algebra", "average_score": 75.3 },
    //         { "subject_name": "Introduction to Python", "average_score": 72.8 },
    //         { "subject_name": "European History", "average_score": 70.1 },
    //         { "subject_name": "Biochemistry", "average_score": 68.9 }
    //     ],
    //     "last_month": [
    //         { "subject_name": "Linear Algebra", "average_score": 95.5 },
    //         { "subject_name": "Calculus", "average_score": 93.0 },
    //         { "subject_name": "Introduction to Python", "average_score": 88.0 },
    //         { "subject_name": "History of Art", "average_score": 86.5 },
    //         { "subject_name": "Data Structures", "average_score": 84.0 },
    //         { "subject_name": "Linguistics", "average_score": 80.5 },
    //         { "subject_name": "Organic Chemistry", "average_score": 78.5 },
    //         { "subject_name": "Macroeconomics", "average_score": 77.0 },
    //         { "subject_name": "Biochemistry", "average_score": 75.0 },
    //         { "subject_name": "European History", "average_score": 72.0 }
    //     ]
    //   },
    //   "top_tags": {
    //     "all_time": [
    //       { "tag_name": "#concept-map", "activity_count": 150 },
    //       { "tag_name": "#formula-sheet", "activity_count": 120 },
    //       { "tag_name": "#practice-questions", "activity_count": 95 },
    //       { "tag_name": "#review", "activity_count": 80 },
    //       { "tag_name": "#flashcards", "activity_count": 75 },
    //       { "tag_name": "#summary", "activity_count": 65 },
    //       { "tag_name": "#definition", "activity_count": 55 },
    //       { "tag_name": "#case-study", "activity_count": 48 },
    //       { "tag_name": "#exam-prep", "activity_count": 40 },
    //       { "tag_name": "#project-notes", "activity_count": 35 }
    //     ],
    //     "last_week": [
    //       { "tag_name": "#practice-questions", "activity_count": 25 },
    //       { "tag_name": "#concept-map", "activity_count": 20 },
    //       { "tag_name": "#exam-prep", "activity_count": 15 },
    //       { "tag_name": "#review", "activity_count": 10 },
    //       { "tag_name": "#formula-sheet", "activity_count": 8 },
    //       { "tag_name": "#summary", "activity_count": 7 },
    //       { "tag_name": "#definition", "activity_count": 5 },
    //       { "tag_name": "#flashcards", "activity_count": 4 },
    //       { "tag_name": "#project-notes", "activity_count": 3 },
    //       { "tag_name": "#case-study", "activity_count": 2 }
    //     ]
    //   }
    // };

    // --- Utility Function 1: Generate 52 Weeks of Mock Data for Line Chart ---
    // function generateWeeklyTrendData(weeks = 52, noteBase = 40, questionBase = 10, exerciseBase = 60) {
    //     let data = [];
    //     let startDate = new Date();
    //     startDate.setDate(startDate.getDate() - (weeks * 7));
        
    //     for (let i = 0; i < weeks; i++) {
    //         let weekStartDate = new Date(startDate);
    //         weekStartDate.setDate(startDate.getDate() + (i * 7));
            
    //         data.push({
    //             week_start_date: weekStartDate.getTime(),
    //             notes_created: Math.floor(noteBase + (Math.random() * 20) - 10 + (i * 0.5)),
    //             questions_asked: Math.floor(questionBase + (Math.random() * 8) - 4 + (i * 0.2)),
    //             exercises_attempted: Math.floor(exerciseBase + (Math.random() * 30) - 15 + (i * 0.7))
    //         });
    //     }
    //     analyticsData.weekly_trends = data;
    // }
    // generateWeeklyTrendData();
    
    // --- Utility Function 2: Generate Daily Activity Data for Heatmap (NEW) ---
    // function generateDailyActivityData(days = 365) {
    //     const dayNames = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    //     let allData = [];
    //     let currentDayIndex = 0;
        
    //     // Initialize 7 series (one for each day)
    //     const heatmapSeries = dayNames.map(day => ({ name: day, data: [] }));

    //     for (let i = 0; i < days; i++) {
    //         const activity = Math.floor(Math.random() * 30); // 0 to 29 activity score
    //         const weekNumber = Math.floor(i / 7) + 1;
    //         const dayOfWeek = dayNames[i % 7];
            
    //         // Push data point {x: Week #, y: Activity Score}
    //         heatmapSeries[i % 7].data.push({ x: `W${weekNumber}`, y: activity });
    //     }
        
    //     // ApexCharts uses series for rows (Y-axis labels)
    //     // We want the Y-axis to be Mon-Sun, so the series names are correct.
    //     return heatmapSeries;
    // }
    // const heatmapActivityData = generateDailyActivityData();

});