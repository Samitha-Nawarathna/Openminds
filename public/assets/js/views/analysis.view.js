import { colors } from "../core/config";

document.addEventListener("DOMContentLoaded", function() {
    // Append canvas elements inside your containers
    document.querySelector(".frequent-topic-graph").innerHTML = '<canvas id="frequentTopicChart"></canvas>';
    document.querySelector(".growth-graph").innerHTML = '<canvas id="growthChart"></canvas>';
    document.querySelector(".top-tag-graph").innerHTML = '<canvas id="topTagChart"></canvas>';

    // Frequent Topic Graph (bar chart, y = count-like values but not raw count)
    const frequentTopicCtx = document.getElementById('frequentTopicChart').getContext('2d');
    new Chart(frequentTopicCtx, {
        type: 'bar',
        data: {
            labels: ['Psychology', 'Math', 'Physics', 'Biology', 'History'],
            datasets: [{
                label: 'Topic Score',
                data: [27, 18, 12, 9, 1], // mock data
                backgroundColor: colors.primary
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, title: { display: true, text: 'Score' } },
                x: { title: { display: true, text: 'Subjects' } }
            },

        }
    });

    // Growth Graph (bar chart, y = % growth)
    const growthCtx = document.getElementById('growthChart').getContext('2d');
    new Chart(growthCtx, {
        type: 'bar',
        data: {
            labels: ['Psychology', 'Math', 'Physics', 'Biology', 'History'],
            datasets: [{
                label: 'Growth % (Last Week)',
                data: [12, -5, 8, 20, -2], // mock % growth
                backgroundColor: function(ctx) {
                    return ctx.raw >= 0 ? colors.success : colors.error;
                }
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, title: { display: true, text: 'Growth %' } },
                x: { title: { display: true, text: 'Subjects' } }
            }
        }
    });

    // Top Tag Graph (stacked bar, create/edit/delete counts)
    const topTagCtx = document.getElementById('topTagChart').getContext('2d');
    new Chart(topTagCtx, {
        type: 'bar',
        data: {
            labels: ['#learning', '#science', '#history', '#math', '#psychology'],
            datasets: [
                { label: 'Create', data: [5, 3, 2, 6, 4], backgroundColor: colors.primary },
                { label: 'Edit',   data: [2, 4, 1, 3, 2], backgroundColor: colors.secondaryBackground },
                { label: 'Delete', data: [1, 0, 1, 2, 1], backgroundColor: colors.yellow }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'top' } },
            scales: {
                x: { stacked: true, title: { display: true, text: 'Tags' } },
                y: { stacked: true, beginAtZero: true, title: { display: true, text: 'Action Count' } }
            }
        }
    });
});

document.addEventListener("DOMContentLoaded", function() {
    // Insert canvas elements inside containers
    document.querySelector(".vote-distribution").innerHTML =
        '<canvas id="voteChart"></canvas>';
    document.querySelector(".question-tag-distribution").innerHTML =
        '<canvas id="questionTagChart"></canvas>';
    document.querySelector(".answer-tag-distribution").innerHTML =
        '<canvas id="answerTagChart"></canvas>';



    // Graph 1: Vote distribution (bar chart with Questions vs Answers)
    new Chart(document.getElementById('voteChart').getContext('2d'), {
        type: 'pie',
        data: {
            labels: ['Questions', 'Answers'],
            datasets: [{
                label: 'Vote Count',
                data: [15, 12], // mock data
                backgroundColor: [colors.primary, colors.success]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: true },
                title: {
                    display: true,
                    text: 'Votes Distribution',
                    font: { family: "'Inter','Segoe UI',sans-serif", size: 16, weight: 'bold' },
                    color: colors.text
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { color: colors.text }
                },
                x: { ticks: { color: colors.text } }
            }
        }
    });

    // Graph 2: Top tags for Questions
    new Chart(document.getElementById('questionTagChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: ['#science', '#history', '#math', '#psychology', '#coding'],
            datasets: [{
                label: 'Question Count',
                data: [6, 4, 3, 2, 1], // mock data
                backgroundColor: colors.primary
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                title: {
                    display: true,
                    text: 'Top Tags (Questions)',
                    font: { family: "'Inter','Segoe UI',sans-serif", size: 16, weight: 'bold' },
                    color: colors.text
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { color: colors.text }
                },
                x: { ticks: { color: colors.text } }
            }
        }
    });

    // Graph 3: Top tags for Answers
    new Chart(document.getElementById('answerTagChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: ['#science', '#math', '#psychology', '#history', '#coding'],
            datasets: [{
                label: 'Answer Count',
                data: [5, 4, 2, 1, 1], // mock data
                backgroundColor: colors.success
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                title: {
                    display: true,
                    text: 'Top Tags (Answers)',
                    font: { family: "'Inter','Segoe UI',sans-serif", size: 16, weight: 'bold' },
                    color: colors.text
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { color: colors.text }
                },
                x: { ticks: { color: colors.text } }
            }
        }
    });
});

document.addEventListener("DOMContentLoaded", function() {
    // Inject canvases for each graph
    document.querySelector(".top-marks-subjects").innerHTML = '<canvas id="topMarksSubjects"></canvas>';
    document.querySelector(".worst-marks-subjects").innerHTML = '<canvas id="worstMarksSubjects"></canvas>';
    document.querySelector(".top-subject-graph").innerHTML = '<canvas id="topSubjectGraph"></canvas>';
    document.querySelector(".top-marks-exercises").innerHTML = '<canvas id="topMarksExercises"></canvas>';
    document.querySelector(".top-marks-questions").innerHTML = '<canvas id="topMarksQuestions"></canvas>';
    document.querySelector(".exercise-vote-graph").innerHTML = '<canvas id="exerciseVoteGraph"></canvas>';

    // CSS variable colors
    const rootStyles = getComputedStyle(document.documentElement);
    const colors = {
        primary: rootStyles.getPropertyValue('--color-primary').trim() || '#6375F1',
        success: rootStyles.getPropertyValue('--color-success').trim() || '#34A57D',
        error: rootStyles.getPropertyValue('--color-error').trim() || '#F27970',
        text: rootStyles.getPropertyValue('--color-text').trim() || '#222'
    };

    // Graph 1: Top marks per subject (bar)
    new Chart(document.getElementById('topMarksSubjects').getContext('2d'), {
        type: 'bar',
        data: {
            labels: ['Math', 'Physics', 'Chemistry', 'Biology', 'History'],
            datasets: [{
                label: 'Top Marks',
                data: [95, 92, 90, 88, 85],
                backgroundColor: colors.primary
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } }
        }
    });

    // Graph 2: Worst marks per subject (bar)
    new Chart(document.getElementById('worstMarksSubjects').getContext('2d'), {
        type: 'bar',
        data: {
            labels: ['Math', 'Physics', 'Chemistry', 'Biology', 'History'],
            datasets: [{
                label: 'Worst Marks',
                data: [45, 50, 48, 40, 35],
                backgroundColor: colors.error
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
    });

    // Graph 3: Top subjects for created exercises (pie)
    new Chart(document.getElementById('topSubjectGraph').getContext('2d'), {
        type: 'pie',
        data: {
            labels: ['Math', 'Physics', 'Chemistry', 'Biology', 'History'],
            datasets: [{
                data: [12, 9, 8, 6, 5],
                backgroundColor: [colors.primary, colors.success, colors.error, '#F6C26B', '#B9EAC0']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { color: colors.text } },
                datalabels: {
                    color: colors.text,
                    formatter: (value) => value
                }
            }
        },
        // plugins: [ChartDataLabels]
    });

    // Graph 4: Top marks per exercise (bar)
    new Chart(document.getElementById('topMarksExercises').getContext('2d'), {
        type: 'bar',
        data: {
            labels: ['Exercise 1', 'Exercise 2', 'Exercise 3', 'Exercise 4', 'Exercise 5'],
            datasets: [{
                label: 'Top Marks',
                data: [98, 95, 92, 90, 88],
                backgroundColor: colors.primary
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
    });

    // Graph 5: Top marks per question (bar)
    new Chart(document.getElementById('topMarksQuestions').getContext('2d'), {
        type: 'bar',
        data: {
            labels: ['Q1', 'Q2', 'Q3', 'Q4', 'Q5'],
            datasets: [{
                label: 'Top Marks',
                data: [10, 9, 9, 8, 7],
                backgroundColor: colors.success
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
    });

    // Graph 6: Exercise vote analysis (pie)
    new Chart(document.getElementById('exerciseVoteGraph').getContext('2d'), {
        type: 'pie',
        data: {
            labels: ['Votes'],
            datasets: [{
                data: [5],
                backgroundColor: colors.primary
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                datalabels: { color: colors.text, formatter: (value) => value }
            }
        },
        // plugins: [ChartDataLabels]
    });
});