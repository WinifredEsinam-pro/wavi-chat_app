window.listenToConversation = function(conversationId) {
    if (!conversationId) return;
    if (!window.Echo || !window.Echo.private) return;

    // Leave previous channel
    if (window._currentChatChannel) {
        try { 
            window.Echo.leave(`chat.${window._currentChatChannel}`); 
        } catch(e) {
            console.log('Error leaving channel:', e);
        }
    }

    // Subscribe to new channel
    window._currentChatChannel = conversationId;
    const channelName = `chat.${conversationId}`;

    window.Echo.private(channelName)
        .listen('.MessageSent', (e) => {
            console.log('Message received via Echo:', e);
            if (window.Livewire) {
                Livewire.dispatch('messageReceived', { event: e });
            }
        })
        .error((error) => {
            console.error('Echo channel error:', error);
        });
};