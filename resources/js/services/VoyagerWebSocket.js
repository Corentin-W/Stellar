/**
 * Voyager WebSocket Service
 *
 * Manages WebSocket connection to Voyager Proxy for real-time updates
 */
class VoyagerWebSocket {
  constructor(url, options = {}) {
    this.url = url;
    this.socket = null;
    this.reconnectAttempts = 0;
    this.maxReconnectAttempts = options.maxReconnectAttempts || 10;
    this.reconnectDelay = options.reconnectDelay || 3000;
    this.listeners = new Map();
    this.isConnected = false;
    this.isReconnecting = false;

    // Auto-connect if enabled
    if (options.autoConnect !== false) {
      this.connect();
    }
  }

  /**
   * Connect to WebSocket server
   */
  connect() {
    if (this.socket && (this.socket.readyState === WebSocket.CONNECTING || this.socket.readyState === WebSocket.OPEN)) {
      console.warn('WebSocket already connected or connecting');
      return;
    }

    console.log('Connecting to Voyager WebSocket:', this.url);

    try {
      this.socket = new WebSocket(this.url);

      this.socket.onopen = () => {
        console.log('✅ WebSocket connected');
        this.isConnected = true;
        this.isReconnecting = false;
        this.reconnectAttempts = 0;
        this.emit('connected');
      };

      this.socket.onclose = (event) => {
        console.log('WebSocket disconnected:', event.code, event.reason);
        this.isConnected = false;
        this.emit('disconnected', { code: event.code, reason: event.reason });

        // Attempt to reconnect
        if (!event.wasClean && this.reconnectAttempts < this.maxReconnectAttempts) {
          this.scheduleReconnect();
        } else if (this.reconnectAttempts >= this.maxReconnectAttempts) {
          console.error('Max reconnection attempts reached');
          this.emit('error', new Error('Max reconnection attempts reached'));
        }
      };

      this.socket.onerror = (error) => {
        console.error('WebSocket error:', error);
        this.emit('error', error);
      };

      this.socket.onmessage = (event) => {
        try {
          const message = JSON.parse(event.data);
          this.handleMessage(message);
        } catch (error) {
          console.error('Failed to parse WebSocket message:', error);
        }
      };

    } catch (error) {
      console.error('Failed to create WebSocket:', error);
      this.emit('error', error);
      this.scheduleReconnect();
    }
  }

  /**
   * Schedule reconnection attempt
   */
  scheduleReconnect() {
    if (this.isReconnecting) {
      return;
    }

    this.isReconnecting = true;
    this.reconnectAttempts++;

    const delay = this.reconnectDelay * this.reconnectAttempts;
    console.log(`Reconnecting in ${delay}ms (attempt ${this.reconnectAttempts}/${this.maxReconnectAttempts})`);

    setTimeout(() => {
      this.isReconnecting = false;
      this.connect();
    }, delay);
  }

  /**
   * Handle incoming message
   */
  handleMessage(message) {
    const { event, data } = message;

    if (!event) {
      console.warn('Received message without event type:', message);
      return;
    }

    // console.log('📨 WebSocket event:', event, data);

    // Emit to specific listeners
    this.emit(event, data);

    // Emit to wildcard listeners
    this.emit('*', { event, data });
  }

  /**
   * Subscribe to an event
   */
  on(event, callback) {
    if (!this.listeners.has(event)) {
      this.listeners.set(event, []);
    }
    this.listeners.get(event).push(callback);

    // Return unsubscribe function
    return () => this.off(event, callback);
  }

  /**
   * Subscribe to an event (once)
   */
  once(event, callback) {
    const wrappedCallback = (data) => {
      callback(data);
      this.off(event, wrappedCallback);
    };
    return this.on(event, wrappedCallback);
  }

  /**
   * Unsubscribe from an event
   */
  off(event, callback) {
    if (!this.listeners.has(event)) {
      return;
    }

    if (!callback) {
      // Remove all listeners for this event
      this.listeners.delete(event);
    } else {
      // Remove specific callback
      const callbacks = this.listeners.get(event);
      const index = callbacks.indexOf(callback);
      if (index > -1) {
        callbacks.splice(index, 1);
      }
    }
  }

  /**
   * Emit event to listeners
   */
  emit(event, data) {
    if (!this.listeners.has(event)) {
      return;
    }

    this.listeners.get(event).forEach(callback => {
      try {
        callback(data);
      } catch (error) {
        console.error(`Error in ${event} listener:`, error);
      }
    });
  }

  /**
   * Send message to server
   */
  send(event, data = {}) {
    if (!this.isConnected || this.socket.readyState !== WebSocket.OPEN) {
      console.warn('Cannot send message, WebSocket not connected');
      return false;
    }

    try {
      this.socket.send(JSON.stringify({ event, data }));
      return true;
    } catch (error) {
      console.error('Failed to send message:', error);
      return false;
    }
  }

  /**
   * Disconnect from WebSocket
   */
  disconnect() {
    if (this.socket) {
      this.socket.close();
      this.socket = null;
    }
    this.isConnected = false;
  }

  /**
   * Get connection state
   */
  getState() {
    return {
      isConnected: this.isConnected,
      isReconnecting: this.isReconnecting,
      reconnectAttempts: this.reconnectAttempts,
      readyState: this.socket?.readyState,
    };
  }
}

// Create singleton instance
let instance = null;

export function createVoyagerWebSocket(url, options = {}) {
  if (!instance) {
    instance = new VoyagerWebSocket(url, options);
  }
  return instance;
}

export function getVoyagerWebSocket() {
  if (!instance) {
    throw new Error('VoyagerWebSocket not initialized. Call createVoyagerWebSocket first.');
  }
  return instance;
}

export default VoyagerWebSocket;
