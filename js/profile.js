document.getElementById('toggleExamplesLink').addEventListener('click', function() {
            const examplesContainer = document.getElementById('examplePromptsContainer');
            examplesContainer.classList.toggle('hidden');
            if (examplesContainer.classList.contains('hidden')) {
                this.textContent = 'See example prompts';
            } else {
                this.textContent = 'Hide example prompts';
            }
        });

        const systemPromptTextarea = document.getElementById('system_prompt_text');
        const charCountDisplay = document.getElementById('charCount');

        systemPromptTextarea.addEventListener('input', function() {
            const currentLength = this.value.length;
            charCountDisplay.textContent = `${currentLength}/500 characters`;

            if (currentLength > 500) {
                charCountDisplay.style.color = 'red'; // Optionally change color if limit exceeds
            } else {
                charCountDisplay.style.color = '#9CA3AF'; // Back to default gray
            }
        });
        // Initialize character count on page load
        charCountDisplay.textContent = `${systemPromptTextarea.value.length}/500 characters`;
