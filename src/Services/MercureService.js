class MercureService {
  constructor() {
    this.eventSource = null;
    this.subscribers = new Map();
    this.isConnected = false;
    this.reconnectAttempts = 0;
    this.maxReconnectAttempts = 5;
    this.baseReconnectDelay = 1000;
    this.baseUrl = 'http://localhost:3000/.well-known/mercure';
    this.activeTopics = new Set();
  }

  /**
   * Se connecter au Hub Mercure avec gestion des tokens JWT
   * @param {string} [token] - Token JWT d'authentification
   */
  async connect(token = null) {
    try {
      this.disconnect();

      const url = new URL(this.baseUrl);
 
      this.activeTopics.forEach(topic => {
        url.searchParams.append('topic', topic);
      });

      const options = {};
      if (token) {
        options.withCredentials = true;
        url.searchParams.append('authorization', `Bearer ${token}`);
      }

      this.eventSource = new EventSource(url.toString(), options);

      this.eventSource.onopen = () => {
        console.log('✅ Mercure connected');
        this.isConnected = true;
        this.reconnectAttempts = 0;
      };

      this.eventSource.onerror = (error) => {
        console.error('❌ Mercure error:', error);
        this.isConnected = false;
        this.handleReconnection();
      };

      this.eventSource.onmessage = (event) => {
        this.handleMessage(event);
      };

    } catch (error) {
      console.error('Connection error:', error);
      this.handleReconnection();
    }
  }

  /**
   * S'abonner à un ou plusieurs topics
   * @param {string|string[]} topics - Topic(s) à écouter
   * @param {function} callback - Fonction à exécuter à la réception
   * @return {function} Fonction de désabonnement
   */
  subscribe(topics, callback) {
    const topicArray = Array.isArray(topics) ? topics : [topics];
    
    topicArray.forEach(topic => {
      if (!this.subscribers.has(topic)) {
        this.subscribers.set(topic, new Set());
      }
      this.subscribers.get(topic).add(callback);
      this.activeTopics.add(topic);
    });
    if (this.eventSource && !this.isConnected) {
      this.connect(this.getCurrentToken());
    }
    return () => this.unsubscribe(topics, callback);
  }

  /**
   * Se désabonner
   */
  unsubscribe(topics, callback) {
    const topicArray = Array.isArray(topics) ? topics : [topics];
    
    topicArray.forEach(topic => {
      if (this.subscribers.has(topic)) {
        const callbacks = this.subscribers.get(topic);
        callbacks.delete(callback);
        
        if (callbacks.size === 0) {
          this.subscribers.delete(topic);
          this.activeTopics.delete(topic);
        }
      }
    });
  }

  /**
   * Gestion des messages entrants
   */
  handleMessage(event) {
    try {
      const data = JSON.parse(event.data);
      const topic = event.lastEventId || data.topic;

      if (this.subscribers.has(topic)) {
        this.subscribers.get(topic).forEach(cb => cb(data, topic));
      }

      this.notifyWildcardSubscribers(topic, data);

    } catch (error) {
      console.error('Message handling error:', error);
    }
  }

  /**
   * Gestion des wildcards (*)
   */
  notifyWildcardSubscribers(topic, data) {
    for (const [subscribedTopic, callbacks] of this.subscribers) {
      if (this.isTopicMatch(topic, subscribedTopic)) {
        callbacks.forEach(cb => cb(data, topic));
      }
    }
  }

  /**
   * Vérifie la correspondance des topics avec wildcards
   */
  isTopicMatch(actualTopic, subscribedTopic) {
    if (subscribedTopic.includes('*')) {
      const regex = new RegExp(`^${subscribedTopic.replace(/\*/g, '.*')}$`);
      return regex.test(actualTopic);
    }
    return actualTopic === subscribedTopic;
  }

  /**
   * Gestion de la reconnexion avec backoff exponentiel
   */
  handleReconnection() {
    if (this.reconnectAttempts >= this.maxReconnectAttempts) {
      console.error('Max reconnection attempts reached');
      return;
    }

    const delay = this.baseReconnectDelay * Math.pow(2, this.reconnectAttempts);
    this.reconnectAttempts++;

    console.log(`Reconnecting in ${delay}ms...`);
    
    setTimeout(() => {
      this.connect(this.getCurrentToken());
    }, delay);
  }

  /**
   * Récupère le token courant depuis l'URL
   */
  getCurrentToken() {
    if (!this.eventSource) return null;
    const url = new URL(this.eventSource.url);
    return url.searchParams.get('authorization')?.replace('Bearer ', '');
  }

  /**
   * Déconnexion propre
   */
  disconnect() {
    if (this.eventSource) {
      this.eventSource.close();
      this.eventSource = null;
      this.isConnected = false;
    }
  }

  /**
   * État courant du service
   */
  getStatus() {
    return {
      connected: this.isConnected,
      reconnectAttempts: this.reconnectAttempts,
      subscribedTopics: Array.from(this.activeTopics),
      subscribers: this.subscribers.size
    };
  }
}

export const mercureService = new MercureService();