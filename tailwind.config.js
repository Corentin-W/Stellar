/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    darkMode: 'class',
    theme: {
        extend: {
            fontFamily: {
                'inter': ['Inter', 'sans-serif'],
                'astral': ['Orbitron', 'monospace'],
            },
            colors: {
                'astral': {
                    50: '#f0f9ff',
                    100: '#e0f2fe',
                    200: '#bae6fd',
                    300: '#7dd3fc',
                    400: '#38bdf8',
                    500: '#667eea',
                    600: '#0284c7',
                    700: '#0369a1',
                    800: '#075985',
                    900: '#0a0a0f',
                    950: '#082f49',
                },
                'cosmic': {
                    50: '#faf5ff',
                    100: '#f3e8ff',
                    200: '#e9d5ff',
                    300: '#d8b4fe',
                    400: '#c084fc',
                    500: '#764ba2',
                    600: '#9333ea',
                    700: '#7c3aed',
                    800: '#6b21a8',
                    900: '#581c87',
                    950: '#3b0764',
                }
            },
            animation: {
                'fade-in-up': 'fadeInUp 0.6s ease-out forwards',
                'slide-in-right': 'slideInRight 0.4s ease-out forwards',
                'glow-pulse': 'glowPulse 2s infinite',
                'twinkle': 'twinkle 8s infinite',
            },
            keyframes: {
                fadeInUp: {
                    '0%': {
                        opacity: '0',
                        transform: 'translateY(20px)'
                    },
                    '100%': {
                        opacity: '1',
                        transform: 'translateY(0)'
                    }
                },
                slideInRight: {
                    '0%': {
                        opacity: '0',
                        transform: 'translateX(100%)'
                    },
                    '100%': {
                        opacity: '1',
                        transform: 'translateX(0)'
                    }
                },
                glowPulse: {
                    '0%, 100%': {
                        boxShadow: '0 0 20px rgba(102, 126, 234, 0.3)'
                    },
                    '50%': {
                        boxShadow: '0 0 30px rgba(102, 126, 234, 0.5)'
                    }
                },
                twinkle: {
                    '0%, 100%': { opacity: '1' },
                    '25%': { opacity: '0.8' },
                    '50%': { opacity: '0.9' },
                    '75%': { opacity: '0.7' }
                }
            },
            backdropBlur: {
                'glass': '20px',
            },
            spacing: {
                '18': '4.5rem',
                '88': '22rem',
            }
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
    ],
}
