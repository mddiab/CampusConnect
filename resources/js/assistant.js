(() => {
                const root = document.querySelector('[data-assistant]');

                if (! root) {
                    return;
                }

                const contextElement = document.getElementById('campusconnect-assistant-context');
                const context = contextElement ? JSON.parse(contextElement.textContent) : {};
                const launcher = root.querySelector('[data-assistant-toggle]');
                const panel = root.querySelector('[data-assistant-panel]');
                const closeButton = root.querySelector('[data-assistant-close]');
                const messagesElement = root.querySelector('[data-assistant-messages]');
                const suggestionsElement = root.querySelector('[data-assistant-suggestions]');
                const form = root.querySelector('[data-assistant-form]');
                const input = root.querySelector('[data-assistant-input]');
                const submitButton = root.querySelector('.assistant-submit');
                const storageKey = 'campusconnect-assistant-v2';
                const contextKey = [
                    context.isAuthenticated ? 'auth' : 'guest',
                    context.role ?? 'guest',
                    context.assistantEnabled ? 'enabled' : 'disabled',
                ].join(':');

                const defaultState = {
                    open: false,
                    contextKey,
                    messages: [],
                    isLoading: false,
                };

                let state = loadState();

                if (! state.messages.length) {
                    state.messages = [buildWelcomeMessage()];
                }

                renderMessages();
                renderSuggestions();
                syncOpenState();
                persistState();

                launcher.addEventListener('click', () => {
                    state.open = ! state.open;
                    syncOpenState(true);
                    persistState();
                });

                closeButton.addEventListener('click', () => {
                    state.open = false;
                    syncOpenState();
                    persistState();
                    launcher.focus();
                });

                form.addEventListener('submit', (event) => {
                    event.preventDefault();

                    const value = input.value.trim();

                    if (! value) {
                        return;
                    }

                    handlePrompt(value);
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape' && state.open) {
                        state.open = false;
                        syncOpenState();
                        persistState();
                        launcher.focus();
                    }
                });

                function loadState() {
                    try {
                        const raw = sessionStorage.getItem(storageKey);

                        if (! raw) {
                            return { ...defaultState };
                        }

                        const parsed = JSON.parse(raw);

                        if (parsed.contextKey !== contextKey) {
                            return { ...defaultState };
                        }

                        return {
                            ...defaultState,
                            ...parsed,
                            messages: Array.isArray(parsed.messages)
                                ? parsed.messages
                                    .slice(-12)
                                    .filter((message) => ['user', 'bot'].includes(message?.role) && typeof message?.text === 'string')
                                : [],
                        };
                    } catch (error) {
                        return { ...defaultState };
                    }
                }

                function persistState() {
                    sessionStorage.setItem(storageKey, JSON.stringify({
                        open: state.open,
                        contextKey,
                        messages: state.messages.slice(-12),
                    }));
                }

                function syncOpenState(shouldFocusInput = false) {
                    root.classList.toggle('is-open', state.open);
                    launcher.setAttribute('aria-expanded', state.open ? 'true' : 'false');
                    panel.setAttribute('aria-hidden', state.open ? 'false' : 'true');

                    if (state.open && shouldFocusInput) {
                        window.setTimeout(() => input.focus(), 80);
                    }
                }

                function renderMessages() {
                    messagesElement.innerHTML = '';

                    state.messages.forEach((message) => {
                        const article = document.createElement('article');
                        article.className = `assistant-message assistant-message-${message.role}`;

                        const paragraph = document.createElement('p');
                        paragraph.className = 'assistant-message-text';
                        paragraph.textContent = message.text;
                        article.appendChild(paragraph);

                        messagesElement.appendChild(article);
                    });

                    if (state.isLoading) {
                        const article = document.createElement('article');
                        article.className = 'assistant-message assistant-message-bot assistant-message-pending';

                        const paragraph = document.createElement('p');
                        paragraph.className = 'assistant-message-text';
                        paragraph.textContent = 'Typing...';
                        article.appendChild(paragraph);

                        messagesElement.appendChild(article);
                    }

                    messagesElement.scrollTop = messagesElement.scrollHeight;
                }

                function renderSuggestions() {
                    suggestionsElement.innerHTML = '';

                    const hasUserMessage = state.messages.some((message) => message.role === 'user');
                    suggestionsElement.hidden = hasUserMessage;

                    if (hasUserMessage) {
                        return;
                    }

                    getSuggestions().forEach((suggestion) => {
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.className = 'assistant-chip';
                        button.textContent = suggestion.label;
                        button.addEventListener('click', () => handlePrompt(suggestion.prompt));
                        suggestionsElement.appendChild(button);
                    });
                }

                function getSuggestions() {
                    if (! context.isAuthenticated) {
                        return [
                            { label: 'How do I sign in?', prompt: 'How do I sign in?' },
                            { label: 'What can students do?', prompt: 'What can students do?' },
                            { label: 'What can staff do?', prompt: 'What can staff do?' },
                            { label: 'What can admins do?', prompt: 'What can admins do?' },
                        ];
                    }

                    if (context.role === 'student') {
                        return [
                            { label: 'How do I submit a request?', prompt: 'How do I submit a request?' },
                            { label: 'What do statuses mean?', prompt: 'What do ticket statuses mean?' },
                            { label: 'Open my dashboard', prompt: 'Take me to my dashboard' },
                            { label: 'How do attachments work?', prompt: 'How do attachments work?' },
                        ];
                    }

                    if (context.role === 'staff') {
                        return [
                            { label: 'Open my queue', prompt: 'Open my queue' },
                            { label: 'Can I change priority?', prompt: 'Can staff change priority?' },
                            { label: 'Who can see this ticket?', prompt: 'Who can see a ticket?' },
                            { label: 'What do statuses mean?', prompt: 'What do ticket statuses mean?' },
                        ];
                    }

                    return [
                        { label: 'Open admin dashboard', prompt: 'Open admin dashboard' },
                        { label: 'Manage users', prompt: 'Where do I manage users?' },
                        { label: 'Manage categories', prompt: 'Where do I manage categories?' },
                        { label: 'Open reports', prompt: 'Where are reports?' },
                    ];
                }

                function buildWelcomeMessage() {
                    if (! context.assistantEnabled) {
                        return {
                            role: 'bot',
                            text: 'Add your Gemini API key to `.env` to enable natural assistant replies here.',
                        };
                    }

                    if (! context.isAuthenticated) {
                        return {
                            role: 'bot',
                            text: 'Ask me about signing in, roles, or where to find something.',
                        };
                    }

                    if (context.role === 'student') {
                        return {
                            role: 'bot',
                            text: 'Ask me about requests, updates, or where to find a page.',
                        };
                    }

                    if (context.role === 'staff') {
                        return {
                            role: 'bot',
                            text: 'Ask me about your queue, statuses, priorities, or staff tools.',
                        };
                    }

                    return {
                        role: 'bot',
                        text: 'Ask me about users, departments, categories, reports, or admin navigation.',
                    };
                }

                async function handlePrompt(prompt) {
                    const normalizedPrompt = prompt.trim();

                    if (! normalizedPrompt || state.isLoading) {
                        return;
                    }

                    const history = serializableHistory();

                    state.open = true;
                    state.messages.push({
                        role: 'user',
                        text: normalizedPrompt,
                    });
                    state.messages = state.messages.slice(-12);
                    input.value = '';
                    setLoading(true);
                    renderMessages();
                    renderSuggestions();
                    syncOpenState();
                    persistState();

                    try {
                        const reply = await requestAssistantReply(normalizedPrompt, history);

                        state.messages.push({
                            role: 'bot',
                            text: reply,
                        });
                    } catch (error) {
                        state.messages.push({
                            role: 'bot',
                            text: error instanceof Error
                                ? error.message
                                : 'The assistant could not reply right now. Please try again.',
                        });
                    } finally {
                        state.messages = state.messages.slice(-12);
                        setLoading(false);
                        renderMessages();
                        renderSuggestions();
                        syncOpenState();
                        persistState();
                        window.setTimeout(() => input.focus(), 80);
                    }
                }

                async function requestAssistantReply(message, history) {
                    const response = await fetch(context.chatEndpoint, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': context.csrfToken,
                        },
                        body: JSON.stringify({
                            message,
                            history,
                            current_route: context.currentRoute,
                        }),
                    });

                    const payload = await parseJson(response);

                    if (! response.ok) {
                        throw new Error(payload?.message || 'The assistant could not reply right now. Please try again.');
                    }

                    if (! payload?.reply || typeof payload.reply !== 'string') {
                        throw new Error('The assistant did not return a usable reply. Please try again.');
                    }

                    return payload.reply.trim();
                }

                function serializableHistory() {
                    return state.messages
                        .slice(-10)
                        .map((message) => ({
                            role: message.role,
                            text: message.text,
                        }));
                }

                function setLoading(isLoading) {
                    state.isLoading = isLoading;
                    input.disabled = isLoading;
                    submitButton.disabled = isLoading;
                }

                async function parseJson(response) {
                    try {
                        return await response.json();
                    } catch (error) {
                        return null;
                    }
                }
            })();
