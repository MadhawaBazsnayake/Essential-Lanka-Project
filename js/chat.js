import { supabase } from './supabase-client.js';

/**
 * Loads all users that the current user has chatted with.
 * Since Supabase doesn't easily do complex distinct OR queries natively without a custom function,
 * we fetch all messages where user is sender or receiver, extract unique IDs, and fetch those user profiles.
 */
export async function loadContacts(currentUserId) {
    try {
        const { data: messages, error } = await supabase
            .from('messages')
            .select('sender_id, receiver_id')
            .or(`sender_id.eq.${currentUserId},receiver_id.eq.${currentUserId}`)
            .order('created_at', { ascending: false });

        if (error) throw error;

        // Get unique contact IDs
        const contactIds = new Set();
        messages.forEach(msg => {
            if (msg.sender_id !== currentUserId) contactIds.add(msg.sender_id);
            if (msg.receiver_id !== currentUserId) contactIds.add(msg.receiver_id);
        });

        if (contactIds.size === 0) return [];

        // Fetch user profiles for these contacts
        const { data: contacts, error: userError } = await supabase
            .from('users')
            .select('id, first_name, last_name, role')
            .in('id', Array.from(contactIds));

        if (userError) throw userError;

        return contacts;

    } catch (error) {
        console.error('Error loading contacts:', error);
        return [];
    }
}

/**
 * Load message history between two users
 */
export async function loadMessages(currentUserId, contactId) {
    try {
        const { data, error } = await supabase
            .from('messages')
            .select('*')
            .or(`and(sender_id.eq.${currentUserId},receiver_id.eq.${contactId}),and(sender_id.eq.${contactId},receiver_id.eq.${currentUserId})`)
            .order('created_at', { ascending: true });

        if (error) throw error;
        return data;
    } catch (error) {
        console.error('Error loading messages:', error);
        return [];
    }
}

/**
 * Send a message
 */
export async function sendMessage(senderId, receiverId, text) {
    try {
        const { error } = await supabase
            .from('messages')
            .insert([{
                sender_id: senderId,
                receiver_id: receiverId,
                message: text
            }]);

        if (error) throw error;
        return true;
    } catch (error) {
        console.error('Error sending message:', error);
        return false;
    }
}

/**
 * Subscribe to new messages for the current user
 */
export function subscribeToMessages(currentUserId, callback) {
    const channel = supabase.channel(`public:messages`)
        .on(
            'postgres_changes',
            { event: 'INSERT', schema: 'public', table: 'messages' },
            (payload) => {
                // Trigger callback if the message involves the current user
                if (payload.new.receiver_id === currentUserId || payload.new.sender_id === currentUserId) {
                    callback(payload);
                }
            }
        )
        .subscribe();
        
    return channel;
}
