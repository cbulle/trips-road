/**
 * @file Messaging system management
 * @description Handles real-time interaction for the messaging form, including
 * AJAX submission, UI auto-resizing, and scrolling.
 */

document.addEventListener('DOMContentLoaded', function () {
    /** @type {HTMLFormElement} */
    const messageForm = document.getElementById('messageForm');
    /** @type {HTMLTextAreaElement} */
    const messageInput = document.getElementById('messageInput');
    /** @type {HTMLElement} */
    const messagesContainer = document.getElementById('messagesContainer');

    if (messageForm) {
        /**
         * Event listener for message submission.
         * Sends data via POST and reloads on success.
         */
        messageForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            const messageText = messageInput.value.trim();
            if (!messageText) return;

            try {
                const response = await fetch(this.action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: new URLSearchParams({
                        'conversation_id': window.conversationId,
                        'recipient_id': window.amiId,
                        'body': messageText
                    })
                });

                const data = await response.json();

                if (data.success) {
                    messageInput.value = '';
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                console.error('Fetch error:', error);
                alert('An error occurred while sending the message');
            }
        });
    }

    /**
     * Auto-expands the textarea height based on content.
     */
    if (messageInput) {
        messageInput.addEventListener('input', function () {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 150) + 'px';
        });
    }

    /**
     * Automatically scrolls the message container to the bottom on load.
     */
    if (messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const chatInput = document.getElementById('chat-input');

    if (chatInput) {
        chatInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();

                if (this.value.trim() !== '') {
                    this.closest('form').submit();
                }
            }
        });

        const container = document.getElementById('messagesContainer');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    }
});
