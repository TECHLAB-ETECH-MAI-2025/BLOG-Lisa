import React, { useState, useEffect, useRef } from 'react';
import { useMercureWithAuth } from '../hooks/useMercure';
import axios from 'axios';

const Chat = ({ chatId, userId }) => {
  const [messages, setMessages] = useState([]);
  const [newMessage, setNewMessage] = useState('');
  const [token, setToken] = useState(null);
  const messagesEndRef = useRef(null);

  // Hook Mercure pour recevoir les messages en temps réel
  const { data: mercureData, isConnected } = useMercureWithAuth(
    `chat/${chatId}/messages`,
    token,
    [chatId]
  );

  // Récupérer le token Mercure au chargement
  useEffect(() => {
    const fetchToken = async () => {
      try {
        const response = await axios.get('/api/chat/mercure-token');
        setToken(response.data.token);
      } catch (error) {
        console.error('Erreur lors de la récupération du token:', error);
      }
    };

    fetchToken();
  }, []);

  // Charger les messages existants
  useEffect(() => {
    const loadMessages = async () => {
      try {
        const response = await axios.get(`/api/chat/${chatId}/messages`);
        setMessages(response.data);
      } catch (error) {
        console.error('Erreur lors du chargement des messages:', error);
      }
    };

    loadMessages();
  }, [chatId]);

  // Écouter les nouveaux messages via Mercure
  useEffect(() => {
    if (mercureData && mercureData.message) {
      setMessages(prev => [...prev, mercureData.message]);
      scrollToBottom();
    }
  }, [mercureData]);

  const scrollToBottom = () => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  };

  const sendMessage = async (e) => {
    e.preventDefault();

    if (!newMessage.trim()) return;

    try {
      await axios.post('/api/chat/send', {
        chatId: parseInt(chatId),
        content: newMessage
      });

      setNewMessage('');
    } catch (error) {
      console.error('Erreur lors de l\'envoi du message:', error);
    }
  };

  return (
    <div>
      <h2>
        Chat {chatId} {isConnected ? '🟢 Connecté' : '🔴 Déconnecté'}
      </h2>

      <div className="messages" style={{ maxHeight: '300px', overflowY: 'auto' }}>
        {messages.map((message, index) => (
          <div key={index}>
            <strong>{message.username}</strong> [{new Date(message.createdAt).toLocaleTimeString()}]: {message.content}
          </div>
        ))}
        <div ref={messagesEndRef} />
      </div>

      <form onSubmit={sendMessage} style={{ marginTop: '1em' }}>
        <input
          type="text"
          value={newMessage}
          onChange={e => setNewMessage(e.target.value)}
          placeholder="Tapez votre message..."
          disabled={!isConnected}
          style={{ width: '80%' }}
        />
        <button type="submit" disabled={!isConnected}>
          Envoyer
        </button>
      </form>
    </div>
  );
};

export default Chat;
