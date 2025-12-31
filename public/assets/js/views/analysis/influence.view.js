import {ROOT} from '../../core/config.js';

document.addEventListener('DOMContentLoaded', function() {
  const API_ENDPOINT = ROOT + 'analysis/api/influence_data';
  const MAX_RETRIES = 5;

  // --- Utility Function: Exponential Backoff Fetch ---
  async function fetchWithExponentialBackoff(url, options = {}, retries = 0) {
      try {
          const response = await fetch(url, options);
          if (!response.ok) {
              throw new Error(`HTTP error! status: ${response.status}`);
          }
          return await response.json();
      } catch (error) {
          if (retries < MAX_RETRIES) {
              const delay = Math.pow(2, retries) * 1000; // 1s, 2s, 4s, 8s, 16s
              // Do not log retries in a user-facing way
              await new Promise(resolve => setTimeout(resolve, delay));
              return fetchWithExponentialBackoff(url, options, retries + 1);
          } else {
              console.error(`[Analytics] Failed to fetch ${url} after ${MAX_RETRIES} retries.`, error);
              // Show error message instead of throwing
              throw new Error('Could not load influence analytics data due to persistent network error.');
          }
      }
  }

  // // --- UI/State Management ---
  // const loadingContainer = document.getElementById('loading-container');
  // const analyticsContent = document.getElementById('analytics-content');

  // function toggleLoading(isLoading) {
  //     if (isLoading) {
  //         loadingContainer.classList.remove('hidden');
  //         analyticsContent.classList.add('hidden');
  //     } else {
  //         loadingContainer.classList.add('hidden');
  //         analyticsContent.classList.remove('hidden');
  //     }
  // }

  // --- 2. GET DESIGN SYSTEM VARIABLES & SET GLOBAL APEXCHARTS THEME ---
  const rootStyles = getComputedStyle(document.documentElement);
  const colorPrimary = rootStyles.getPropertyValue('--color-primary-accent').trim() || '#3b82f6';
  const colorSecondary = rootStyles.getPropertyValue('--color-secondary-accent').trim() || '#10b981';
  const colorTertiary = rootStyles.getPropertyValue('--color-tertiary-accent').trim() || '#f59e0b';
  const colorTextLight = rootStyles.getPropertyValue('--color-text-light').trim() || '#4b5563';
  const colorTextDark = rootStyles.getPropertyValue('--color-text-dark').trim() || '#1f2937';
  const fontFamily = rootStyles.getPropertyValue('--font-family-primary').trim() || 'Inter, sans-serif';
  const colorBorder = rootStyles.getPropertyValue('--color-border').trim() || '#e5e7eb';

  Apex.theme = {
      mode: 'light',
      chart: { fontFamily: fontFamily, foreColor: colorTextLight, toolbar: { show: false } },
      grid: { borderColor: colorBorder },
      xaxis: { axisBorder: { color: colorBorder }, axisTicks: { color: colorBorder } },
      yaxis: { lines: { show: true } },
      dataLabels: { style: { fontFamily: fontFamily } },
      tooltip: { theme: 'light', style: { fontFamily: fontFamily } }
  };

  // --- 3. CHART & TABLE RENDERING FUNCTIONS ---

  function renderQAContribution(qaData) {
      const qaChartOptions = {
          series: [{ 
              name: 'Count', 
              data: qaData.map(d => d.count) 
          }],
          chart: { type: 'bar', height: 300 },
          colors: [colorPrimary],
          plotOptions: {
              bar: {
                  horizontal: false,
                  columnWidth: '85%',
                  endingShape: 'rounded'
              }
          },
          dataLabels: { enabled: true, style: { colors: [colorTextDark] } },
          xaxis: {
              categories: qaData.map(d => d.name),
              labels: { show: true, style: { colors: colorTextDark } },
              axisBorder: { show: false },
              axisTicks: { show: false }
          },
          yaxis: {
              title: { text: 'Total Count', style: { fontWeight: 500 } },
              min: 0,
              labels: {
                  formatter: (val) => Math.round(val) // Ensure Y-axis shows integers
              }
          },
          legend: { show: false },
          grid: { show: false }
      };
      
      const qaContributionChart = new ApexCharts(document.querySelector("#qa-contribution-chart-container"), qaChartOptions);
      qaContributionChart.render();
  }

  function renderWeeklyVoteTrends(weeklyVoteData) {
      const votesOptions = {
          series: [
              { name: 'Votes Given', data: weeklyVoteData.map(d => ({ x: d.date, y: d.votes })) }
          ],
          chart: { type: 'line', height: 350 },
          colors: [colorSecondary],
          xaxis: {
              type: 'datetime',
              labels: {
                  formatter: (val) => new Date(val).toLocaleDateString('en-US', { month: 'short', year: '2-digit' })
              }
          },
          yaxis: {
              title: { text: 'Votes Given', style: { fontWeight: 500 } },
              min: 0
          },
          stroke: { curve: 'smooth', width: 3 },
          legend: { show: false },
          tooltip: {
              x: { format: 'MMM dd, yyyy' },
              y: { formatter: (val) => val.toLocaleString() }
          }
      };
      
      const weeklyVotesChart = new ApexCharts(document.querySelector("#weekly-votes-chart-container"), votesOptions);
      weeklyVotesChart.render();
  }

  function renderTopAnswersTable(topAnswers) {
      const tableBody = document.getElementById('top-answers-table-body');
      tableBody.innerHTML = ''; // Clear previous content

      topAnswers.forEach(answer => {
          const row = document.createElement('tr');
          row.className = 'hover:bg-gray-50';
          
          // Question Column
          const questionCell = document.createElement('td');
          questionCell.className = 'px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 max-w-xs truncate';
          const questionLink = document.createElement('a');
          questionLink.href = answer.link || '#';
          questionLink.textContent = answer.question;
          questionLink.className = 'text-blue-600 hover:text-blue-800 transition duration-150 ease-in-out';
          questionCell.appendChild(questionLink);
          row.appendChild(questionCell);
          
          // Vote Count Column
          const votesCell = document.createElement('td');
          votesCell.className = 'px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-semibold';
          votesCell.textContent = answer.votes.toLocaleString();
          row.appendChild(votesCell);
          
          // Accepted Status Column (Pill)
          const acceptedCell = document.createElement('td');
          acceptedCell.className = 'px-6 py-4 whitespace-nowrap text-sm';
          const pill = document.createElement('span');
          
          if (answer.accepted) {
              pill.className = 'accepted-pill';
              pill.textContent = 'Accepted';
          } else {
              pill.className = 'unaccepted-pill';
              pill.textContent = 'Pending';
          }
          
          acceptedCell.appendChild(pill);
          row.appendChild(acceptedCell);
          
          tableBody.appendChild(row);
      });
  }


  // --- 4. Main Data Loading and Rendering Logic ---
  async function loadInfluenceData() {
      // toggleLoading(true);
      try {
          const data = await fetchWithExponentialBackoff(API_ENDPOINT);
          
          // The backend data structure matches the frontend expectations:
          // data.qa_contribution, data.top_answers, data.weekly_vote_data
          
          renderQAContribution(data.qa_contribution);
          renderWeeklyVoteTrends(data.weekly_vote_data);
          renderTopAnswersTable(data.top_answers);

      } catch (error) {
          console.error("[Analytics Error]", error);
          // Display an error message to the user instead of a chart/table
          loadingContainer.innerHTML = `<div class="text-red-600 font-medium text-center">
              <svg class="h-8 w-8 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
              <p>Error loading influence data.</p>
              <p class="text-sm text-red-500 mt-1">${error.message}</p>
          </div>`;
          return;
      } finally {
          toggleLoading(false);
      }
  }

  loadInfluenceData();
});