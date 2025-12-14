// LiveMonitor Component - Monitoring en temps réel de sessions RoboTarget
export default function LiveMonitor(sessionId, userId) {
    return {
        sessionId: sessionId,
        userId: userId,
        connected: false,
        session: null,
        progress: {
            percentage: 0,
            currentShot: 0,
            totalShots: 0,
            remaining: 0
        },
        camera: {
            temperature: null,
            cooling: false,
            hfd: null
        },
        mount: {
            ra: null,
            dec: null,
            tracking: false
        },
        images: [],
        maxImages: 20,
        currentImage: null,
        notifications: [],
        telemetry: {
            temperature: [],
            hfd: [],
            maxPoints: 50
        },

        init() {
            console.log('LiveMonitor initializing...', { sessionId: this.sessionId, userId: this.userId });
            this.connectWebSocket();
            this.requestNotificationPermission();
        },

        connectWebSocket() {
            if (!window.Echo) {
                console.error('Laravel Echo not initialized');
                return;
            }

            // Subscribe to user's private channel
            window.Echo.private(`user.${this.userId}`)
                .listen('.session.started', (e) => {
                    console.log('Session started:', e);
                    this.handleSessionStarted(e);
                })
                .listen('.session.progress', (e) => {
                    console.log('Progress update:', e);
                    this.handleProgress(e);
                })
                .listen('.image.ready', (e) => {
                    console.log('Image ready:', e);
                    this.handleImageReady(e);
                })
                .listen('.session.completed', (e) => {
                    console.log('Session completed:', e);
                    this.handleSessionCompleted(e);
                });

            // Subscribe to specific session channel
            window.Echo.private(`robotarget.session.${this.sessionId}`)
                .listen('.session.progress', (e) => {
                    this.handleProgress(e);
                })
                .listen('.image.ready', (e) => {
                    this.handleImageReady(e);
                })
                .listen('.session.completed', (e) => {
                    this.handleSessionCompleted(e);
                });

            this.connected = true;
            this.addNotification('Connecté au monitoring en temps réel', 'success');
        },

        handleSessionStarted(event) {
            this.session = event.session;
            this.addNotification(`Session démarrée: ${event.session.target_name}`, 'info');
            this.showBrowserNotification('Session Démarrée', `Observation de ${event.session.target_name} en cours`);
        },

        handleProgress(event) {
            if (event.progress) {
                this.progress = {
                    ...this.progress,
                    ...event.progress
                };
            }

            // Update camera data
            if (event.progress.camera) {
                this.camera = {
                    ...this.camera,
                    ...event.progress.camera
                };

                // Add to telemetry
                if (event.progress.camera.temperature !== null) {
                    this.addTelemetryPoint('temperature', event.progress.camera.temperature);
                }
                if (event.progress.camera.hfd !== null) {
                    this.addTelemetryPoint('hfd', event.progress.camera.hfd);
                }
            }

            // Update mount data
            if (event.progress.mount) {
                this.mount = {
                    ...this.mount,
                    ...event.progress.mount
                };
            }
        },

        handleImageReady(event) {
            const image = {
                ...event.image,
                timestamp: new Date(event.timestamp),
                id: Date.now()
            };

            // Add to images array (keep only last N images)
            this.images.unshift(image);
            if (this.images.length > this.maxImages) {
                this.images = this.images.slice(0, this.maxImages);
            }

            // Set as current image
            this.currentImage = image;

            // Notification
            this.addNotification(`Nouvelle image: ${image.filter || 'N/A'} ${image.exposure}s`, 'success');
            this.showBrowserNotification('Nouvelle Image', `HFD: ${image.hfd?.toFixed(2) || 'N/A'}`);

            // Play sound
            this.playSound();
        },

        handleSessionCompleted(event) {
            this.session = event.session;
            this.addNotification(`Session terminée: ${event.session.images_accepted} images acceptées`, 'success');
            this.showBrowserNotification(
                'Session Terminée',
                `${event.session.images_accepted}/${event.session.images_captured} images acceptées`
            );
        },

        addTelemetryPoint(type, value) {
            this.telemetry[type].push({
                value,
                timestamp: Date.now()
            });

            // Keep only last N points
            if (this.telemetry[type].length > this.telemetry.maxPoints) {
                this.telemetry[type].shift();
            }
        },

        addNotification(message, type = 'info') {
            const notification = {
                id: Date.now(),
                message,
                type,
                timestamp: new Date()
            };

            this.notifications.unshift(notification);

            // Auto-remove after 5 seconds
            setTimeout(() => {
                this.notifications = this.notifications.filter(n => n.id !== notification.id);
            }, 5000);
        },

        async requestNotificationPermission() {
            if ('Notification' in window && Notification.permission === 'default') {
                await Notification.requestPermission();
            }
        },

        showBrowserNotification(title, body) {
            if ('Notification' in window && Notification.permission === 'granted') {
                new Notification(title, {
                    body,
                    icon: '/favicon.ico',
                    badge: '/favicon.ico',
                    tag: 'robotarget-session'
                });
            }
        },

        playSound() {
            const audio = new Audio('/sounds/camera-shutter.mp3');
            audio.volume = 0.3;
            audio.play().catch(() => {});
        },

        formatDuration(seconds) {
            if (!seconds) return 'N/A';
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            return `${hours}h ${minutes}m`;
        },

        formatTemperature(temp) {
            return temp !== null ? `${temp.toFixed(1)}°C` : 'N/A';
        },

        formatHFD(hfd) {
            return hfd !== null ? hfd.toFixed(2) : 'N/A';
        },

        getProgressBarColor() {
            if (this.progress.percentage >= 80) return 'bg-green-500';
            if (this.progress.percentage >= 50) return 'bg-blue-500';
            if (this.progress.percentage >= 25) return 'bg-yellow-500';
            return 'bg-purple-500';
        },

        destroy() {
            if (window.Echo) {
                window.Echo.leave(`user.${this.userId}`);
                window.Echo.leave(`robotarget.session.${this.sessionId}`);
            }
        }
    };
}

window.LiveMonitor = LiveMonitor;
