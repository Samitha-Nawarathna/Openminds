import { ROOT } from "../../core/config.js";

document.addEventListener('DOMContentLoaded', function () {
    const API_ENDPOINT = ROOT + 'analysis/api/reflection_data';
    const MAX_RETRIES = 5;

    // --- Utility Function: Exponential Backoff Fetch ---
    async function fetchWithExponentialBackoff(url, options = {}, retries = 0) {
        try {
            const response = await fetch(url, options);
            if (!response.ok) {
                // If it's a 4xx or 5xx error, throw it unless it's the last retry
                if (retries < MAX_RETRIES) {
                    throw new Error(`HTTP status ${response.status}`);
                }
                const errorText = await response.text();
                throw new Error(`Failed to load data: ${errorText || response.statusText}`);
            }
            return await response.json();
        } catch (error) {
            if (retries < MAX_RETRIES) {
                const delay = Math.pow(2, retries) * 1000; // 1s, 2s, 4s, 8s, 16s
                console.warn(`[Analytics] Retrying fetch in ${delay / 1000}s... (Attempt ${retries + 1}/${MAX_RETRIES})`, error);
                await new Promise(resolve => setTimeout(resolve, delay));
                return fetchWithExponentialBackoff(url, options, retries + 1);
            } else {
                console.error(`[Analytics] Failed to fetch ${url} after ${MAX_RETRIES} retries.`, error);
                // Show a user-friendly error message on the dashboard area
                const errorElement = document.createElement('div');
                errorElement.className = 'alert alert-danger';
                errorElement.textContent = '⚠️ Could not load reflection analytics data due to a persistent network or server error.';
                document.querySelector('.analytics-dashboard-main') // Replace with a specific container ID if available
                    .prepend(errorElement);
                throw new Error('Could not load reflection analytics data.');
            }
        }
    }

    // --- 1. GET DESIGN SYSTEM VARIABLES & SET GLOBAL APEXCHARTS THEME ---
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
    // 📊 CORE RENDERING FUNCTION
    // ========================================

    function renderAnalytics(data) {
        // Use the fetched data object 'data' instead of the mock 'reflectionData'

        // --- IMPLEMENTATION 1: OVERVIEW PANEL (KPI Cards) ---
        const overviewPanel = document.getElementById('reflection-overview-panel');
        const overviewData = data.overview;

        const kpiDefinitions = {
            // valueKey: points to the field in the API response that holds the main value
            "avg_notes_per_week": { unit: "", format: v => v, valueKey: 'count' },
            "avg_mark_improvement": { unit: "pts", format: v => v.toFixed(1), valueKey: 'score' },
            "note_exercise_fraction": { unit: "", format: v => overviewData.note_exercise_fraction.fraction, valueKey: 'fraction' } // The fraction key holds the display string
        };

        // Clear any loading states or mock data
        overviewPanel.innerHTML = '';

        ['avg_notes_per_week', 'avg_mark_improvement', 'note_exercise_fraction'].forEach(key => {
            const itemData = overviewData[key];
            const def = kpiDefinitions[key];

            // Handle specific value extraction
            const value = itemData[def.valueKey];
            const valueDisplay = (key === 'note_exercise_fraction') ? itemData.fraction : def.format(value);

            const isPositive = itemData.change_percentage >= 0;
            const changeClass = isPositive ? 'positive' : 'negative';
            const sign = isPositive ? '↑' : '↓';

            const html = `
                <div class="kpi-card">
                    <div class="kpi-title">${itemData.title}</div>
                    <div class="kpi-value">${valueDisplay}${def.unit}</div>
                    <div class="kpi-change ${changeClass}">
                        <span>${sign} ${Math.abs(itemData.change_percentage).toFixed(1)}%</span>
                        <span style="font-weight: 400; color: ${colorTextLight}; margin-left: 0.5rem;">vs. prior period</span>
                    </div>
                </div>
            `;
            overviewPanel.insertAdjacentHTML('beforeend', html);
        });


        // --- IMPLEMENTATION 2: WEEKLY NOTE ACTIVITY LINE CHART (52 WEEKS) ---
        const noteActivityData = data.weekly_note_activity;

        const noteActivityOptions = {
            series: [
                // Note: The 'date' field from the PHP model is already a millisecond Unix timestamp
                { name: 'Created', data: noteActivityData.map(d => ({ x: d.date, y: d.created })) },
                { name: 'Updated', data: noteActivityData.map(d => ({ x: d.date, y: d.updated })) },
                { name: 'Deleted', data: noteActivityData.map(d => ({ x: d.date, y: d.deleted })) }
            ],
            chart: { type: 'bar', height: 350, stacked: true },
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
            legend: { show: true, position: 'top', horizontalAlign: 'right' },
            tooltip: {
                x: { format: 'MMM dd, yyyy' },
                y: { formatter: (val) => val.toLocaleString() }
            }
        };

        const noteActivityChart = new ApexCharts(document.querySelector("#note-activity-chart-container"), noteActivityOptions);
        noteActivityChart.render();


        // --- IMPLEMENTATION 3: SUBJECT PROFICIENCY LINE CHART (Dynamic Selector) ---
        const subjectSelector = document.getElementById('subject-selector');
        let subjectProficiencyChart;
        const subjectProficiencyData = data.subject_proficiency;

        // Populate Dropdown
        subjectProficiencyData.forEach((subject, index) => {
            const option = document.createElement('option');
            option.value = index;
            option.textContent = subject.name;
            subjectSelector.appendChild(option);
        });

        function renderProficiencyChart(subjectIndex) {
            const subject = subjectProficiencyData[subjectIndex];
            // Categories are now generated dynamically based on the length of the weekly_marks array
            const categories = Array.from({ length: subject.weekly_marks.length }, (_, i) => `W${i + 1}`);

            const proficiencyOptions = {
                series: [{ name: subject.name, data: subject.weekly_marks }],
                chart: { type: 'line', height: 350, id: 'proficiency-chart' },
                colors: [colorTertiary],
                xaxis: {
                    categories: categories,
                    title: { text: 'Week Number (Last 52)', style: { fontWeight: 500 } },
                    // Only show every 5th label to declutter the axis
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
                // If chart exists, update options and series
                subjectProficiencyChart.updateOptions(proficiencyOptions);
            } 
            else {
                // If chart doesn't exist, create it
                subjectProficiencyChart = new ApexCharts(document.querySelector("#subject-proficiency-chart-container"), proficiencyOptions);
                subjectProficiencyChart.render();
            }
        }

        // Initial render (if data exists)
        if (subjectProficiencyData.length > 0) {
            renderProficiencyChart(0);

            // Add Event Listener
            subjectSelector.addEventListener('change', (e) => {
                renderProficiencyChart(parseInt(e.target.value));
            });
        }


        // --- IMPLEMENTATION 4: TOP POPULAR TAGS (Stacked Bar) ---
        const tagData = data.top_tags_last_4_weeks.sort((a, b) => b.total - a.total).slice(0, 10);
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


        // IMPLEMENTATION 5 (ATTENTION DRIFT) COMPLETELY REMOVED BY USER REQUEST
    }


    // ========================================
    // 🚀 INITIALIZE ANALYTICS ON LOAD
    // ========================================

    // Asynchronously fetch data and then render the charts
    async function initAnalytics() {
        try {
            // Display a simple loading state while fetching
            document.getElementById('reflection-overview-panel').innerHTML = '<div class="loading-spinner">Loading Overview...</div>';

            const realData = await fetchWithExponentialBackoff(API_ENDPOINT);

            // Remove the loading state and render the real data
            renderAnalytics(realData);

        } catch (error) {
            // The fetchWithExponentialBackoff handles the console error and display of the user-facing message
            // No need to re-throw or handle here, but we can log for completeness
            console.error("Initialization failed:", error.message);
        }
    }

    initAnalytics();

});