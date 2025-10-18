       import {ROOT} from "../../core/config.js";
       
       // --- Configuration ---
       const QUESTIONS_PER_LOAD = 10; // Number of questions to load per request
       let currentTab = initial_tab;
       let currentOffset = 0;
       let isFilterActive = false;
       
       // --- Mock AJAX function to replace later ---
       /**
        * Simulates an AJAX call to the backend to fetch questions.
        * @param {string} tabType - 'all', 'your', or 'answered'.
        * @param {number} offset - The starting index for fetching.
        * @param {string} tagFilter - The tag to filter by.
        * @returns {Promise<Object>} Mock response data.
        */
       function mockFetchQuestions(tabType, offset, tagFilter) {
           console.log(`[MOCK AJAX] Fetching ${tabType} questions from offset ${offset} with tag: "${tagFilter}"`);
           
           // In a real application, this would use fetch() or XMLHttpRequest
           // e.g., fetch('/api/questions?tab=' + tabType + '&offset=' + offset + '&limit=' + QUESTIONS_PER_LOAD + '&tag=' + tagFilter)

           // Simulate server-side processing delay
           return new Promise(resolve => {
               setTimeout(() => {
                   // --- MOCK SERVER-SIDE DATA GENERATION ---
                   // This function is implemented in PHP for the initial load, 
                   // but for JS simulation, we use a simple hardcoded block.
                   let mockData = mock_questions;
                   
                   // To test 'Load More', manually create more data
                   if (offset > 0) {
                       mockData = {
                           questions: [
                               { id: 'q' + (offset + 1), title: 'Additional Question ' + (offset + 1), tag: 'Test', creator_id: 'user_X' },
                               { id: 'q' + (offset + 2), title: 'Additional Question ' + (offset + 2), tag: 'Test', creator_id: 'user_X' }
                           ],
                           has_more: offset === 0 // Ensure 'has_more' eventually becomes false
                       };
                   }
                   // --- END MOCK SERVER-SIDE DATA GENERATION ---

                   resolve(mockData);
               }, 400); // 400ms delay simulation
           });
       }
       
       // --- RENDERING FUNCTIONS ---

       /**
        * Creates the HTML structure for a single question item.
        */
       function createQuestionItem(question) {
           const item = document.createElement('div');
           item.classList.add('question-item');
           
           // In a real app, clicking should navigate to the question view
           item.setAttribute('onclick', `window.location.href='/question/${question.id}'`);

           item.innerHTML = `
               <span class="question-title"><a href="${ROOT}question/show?id=${question.id}" class="no-style-link">${question.title}</a></span>
           `;
           return item;
       }

       /**
        * Clears the list and updates the global state before fetching new data.
        */
       async function loadQuestions(tabType, offset) {
           currentTab = tabType;
           currentOffset = 0;
           
           // Update active tab styling
           document.querySelectorAll('.tab-button').forEach(btn => {
               btn.classList.remove('active');
           });
           document.querySelector(`.tab-button[data-tab="${tabType}"]`).classList.add('active');

           // Show a loading state
           const listContainer = document.getElementById('questions-list');
           listContainer.innerHTML = '<div class="loading">Loading questions...</div>';
           
           // Clear filter for a new tab load
           document.getElementById('tag-filter-input').value = '';
           isFilterActive = false;

           const tagFilter = document.getElementById('tag-filter-input').value;

           // Fetch data
           try {
               const response = await mockFetchQuestions(tabType, offset, tagFilter);
               listContainer.innerHTML = ''; // Clear loading state

               response.questions.forEach(q => {
                   listContainer.appendChild(createQuestionItem(q));
               });
               
               // Set the next offset
               currentOffset = response.questions.length;

               // Update 'Load More' button visibility
               let loadMoreBtn = document.getElementById('load-more-btn');

               if (response.has_more) {
                   loadMoreBtn.textContent = 'Load More';
                   loadMoreBtn.disabled = false;
               } else {
                   loadMoreBtn.style.display = 'none';
               }

           } catch (error) {
               listContainer.innerHTML = '<div class="error">Failed to load questions.</div>';
               document.getElementById('load-more-btn').style.display = 'none';
               console.error("Error loading questions:", error);
           }
       }
       
       /**
        * Appends the next set of questions to the list.
        */
       async function loadMore() {
           const tagFilter = document.getElementById('tag-filter-input').value;
           const loadMoreBtn = document.getElementById('load-more-btn');
           
           loadMoreBtn.textContent = 'Loading...';
           loadMoreBtn.disabled = true;

           try {
               const response = await mockFetchQuestions(currentTab, currentOffset, tagFilter);
               
               response.questions.forEach(q => {
                   document.getElementById('questions-list').appendChild(createQuestionItem(q));
               });

               // Update offset
               currentOffset += response.questions.length;
               
               // Update 'Load More' button visibility
               if (response.has_more) {
                   loadMoreBtn.textContent = 'Load More';
                   loadMoreBtn.disabled = false;
               } else {
                   loadMoreBtn.style.display = 'none';
               }

           } catch (error) {
               loadMoreBtn.textContent = 'Failed to Load';
               loadMoreBtn.disabled = false;
               console.error("Error loading more questions:", error);
           }
       }

       /**
        * Handles filtering by tag, resets the offset, and calls loadQuestions.
        */
       function filterQuestions() {
            isFilterActive = true;
            // When filtering, we essentially perform a new load from offset 0
            loadQuestions(currentTab, 0); 
       }

       // Initialize the view on page load
       window.onload = function() {
           loadQuestions(initial_tab, 0);
       };

         // Wait until DOM is fully ready
        document.addEventListener("DOMContentLoaded", () => {

            // --- Filter Button ---
            const filterBtn = document.getElementById("filter-btn");
            filterBtn.addEventListener("click", () => {
            filterQuestions();
            });

            // --- Tab Buttons ---
            const tabs = document.querySelectorAll(".tab-button");
            tabs.forEach(tab => {
            tab.addEventListener("click", () => {
                // remove active class from all
                tabs.forEach(t => t.classList.remove("active"));
                // add active to clicked
                tab.classList.add("active");

                const tabType = tab.dataset.tab;
                loadQuestions(tabType, 0);
            });
            });

            // --- Load More Button ---
            const loadMoreBtn = document.getElementById("load-more-btn");
            loadMoreBtn.addEventListener("click", () => {
            loadMore();
            });

        });