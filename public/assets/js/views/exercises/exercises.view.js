// Filtering and search logic for exercises UI

document.addEventListener('DOMContentLoaded', function() {
    const subjectSelect = document.getElementById('subjectSelect');
    const attempted = document.getElementById('attempted');
    const notAttempted = document.getElementById('notAttempted');
    const createdByYou = document.getElementById('createdByYou');
    const applyFilters = document.getElementById('applyFilters');
    const searchInput = document.getElementById('searchInput');
    const exercisesList = document.getElementById('exercisesList');
    const loadMoreBtn = document.getElementById('loadMoreBtn');
    const openFiltersBtn = document.getElementById('openFilters');
    const closeFiltersBtn = document.getElementById('closeFilters');
    const filtersPanel = document.getElementById('filtersPanel');
    const filtersOverlay = document.getElementById('filtersOverlay');

    // Make attempted and notAttempted mutually exclusive
    attempted.addEventListener('change', function() {
        if (attempted.checked) notAttempted.checked = false;
    });
    notAttempted.addEventListener('change', function() {
        if (notAttempted.checked) attempted.checked = false;
    });

    // Allow user to deselect subjects by clicking again (for browsers that don't support it natively)
    subjectSelect.addEventListener('mousedown', function(e) {
        e.preventDefault();
        var option = e.target;
        option.selected = !option.selected;
        filterExercises();
    });

    function filterExercises() {
        const selectedSubjects = Array.from(subjectSelect.selectedOptions).map(opt => opt.text);
        const statusFilters = [];
        if (attempted.checked) statusFilters.push('attempted');
        if (notAttempted.checked) statusFilters.push('notAttempted');
        if (createdByYou.checked) statusFilters.push('createdByYou');
        const searchText = searchInput.value.toLowerCase();

        Array.from(exercisesList.children).forEach(card => {
            const subject = card.getAttribute('data-subject');
            const status = card.getAttribute('data-status');
            const title = card.querySelector('h4').textContent.toLowerCase();
            let show = true;
            if (selectedSubjects.length && !selectedSubjects.includes(subject)) show = false;
            if (statusFilters.length && !statusFilters.includes(status)) show = false;
            if (searchText && !title.includes(searchText)) show = false;
            card.style.display = show ? '' : 'none';
        });
    }

    applyFilters.addEventListener('click', filterExercises);
    searchInput.addEventListener('input', filterExercises);

    // Load More button (for demo, just disables itself)
    loadMoreBtn.addEventListener('click', function() {
        loadMoreBtn.disabled = true;
        loadMoreBtn.textContent = 'No more exercises';
    });

    // Create Exercise button (for demo)
    document.getElementById('createExerciseBtn').addEventListener('click', function() {
        alert('Create Exercise functionality coming soon!');
    });

    // Responsive filter panel logic
    openFiltersBtn.addEventListener('click', function() {
        filtersPanel.classList.add('open');
        filtersOverlay.classList.add('open');
    });
    closeFiltersBtn.addEventListener('click', function() {
        filtersPanel.classList.remove('open');
        filtersOverlay.classList.remove('open');
    });
    filtersOverlay.addEventListener('click', function() {
        filtersPanel.classList.remove('open');
        filtersOverlay.classList.remove('open');
    });
});
