# Laravel Forum - Real-Time Discussion Platform

<p align="center">
    <img src="https://img.shields.io/badge/Laravel-10+-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
    <img src="https://img.shields.io/badge/Vue.js-3-4FC08D?style=for-the-badge&logo=vue.js&logoColor=white" alt="Vue.js">
    <img src="https://img.shields.io/badge/Inertia.js-9553E9?style=for-the-badge&logo=inertia&logoColor=white" alt="Inertia.js">
    <img src="https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS">
</p>

A modern, feature-rich forum application built with Laravel and Vue.js, featuring real-time notifications and interactive user engagement. This project demonstrates advanced Laravel development practices with WebSocket integration for instant user interactions.

## ✨ Features

### 🔔 Real-Time Notifications
- **Instant Notifications**: Users receive immediate notifications when their posts or comments are liked
- **Live Updates**: Real-time notification count badges and dropdown updates
- **WebSocket Integration**: Powered by Laravel Reverb for seamless real-time communication
- **Smart State Management**: Optimized with Pinia for reactive UI updates

### 💬 Forum Functionality
- **Post Management**: Create, view, and organize posts by topics
- **Comment System**: Threaded discussions with nested comment support
- **Like System**: Interactive like/unlike functionality for posts and comments
- **User Profiles**: Complete user management with Laravel Jetstream
- **Topic Organization**: Categorize discussions for better content discovery

### 🎨 User Experience
- **Responsive Design**: Mobile-first design with Tailwind CSS
- **Scrollable Notifications**: Elegant notification dropdown with custom scrollbars
- **Optimistic Updates**: Instant UI feedback before server confirmation
- **Loading States**: Smooth loading indicators and transitions

## 🏗️ Technical Architecture

### Backend Stack
- **Laravel 10+**: Modern PHP framework with latest features
- **Laravel Reverb**: Official WebSocket server for real-time features
- **Laravel Sanctum**: API authentication for secure endpoints
- **Laravel Jetstream**: Complete authentication scaffolding
- **Event-Driven Design**: Clean separation with Events & Listeners

### Frontend Stack
- **Vue.js 3**: Composition API with reactive data binding
- **Inertia.js**: SPA experience without API complexity
- **Pinia**: Modern state management for Vue applications
- **Tailwind CSS**: Utility-first CSS framework
- **Heroicons**: Beautiful SVG icons

## 🚀 Getting Started

### Prerequisites
- PHP 8.1+
- Node.js 18+
- Composer
- MySQL/PostgreSQL

## 🎯 Key Features Explained

### Real-Time Notification Flow
1. **User Action**: User likes a post/comment
2. **Event Dispatch**: `LikeCreated` event is fired
3. **Listener Execution**: `SendLikeNotification` processes the event
4. **Database Storage**: Notification saved to database
5. **WebSocket Broadcast**: Real-time event sent to user's private channel
6. **Frontend Update**: Vue component receives and displays notification instantly


## 📈 Performance Features

- **Optimized Queries**: Efficient database queries with eager loading
- **Caching Strategy**: Redis caching for frequently accessed data
- **Asset Optimization**: Vite bundling with code splitting
- **Real-time Efficiency**: Direct state updates instead of API polling

## 🔒 Security

- **CSRF Protection**: Laravel's built-in CSRF protection
- **API Authentication**: Sanctum tokens for secure API access
- **Input Validation**: Comprehensive request validation
- **Authorization Policies**: Model-based authorization

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 🙏 Acknowledgments

- [Laravel](https://laravel.com) - The PHP framework for web artisans
- [Vue.js](https://vuejs.org) - The progressive JavaScript framework
- [Inertia.js](https://inertiajs.com) - Build single-page apps without building an API
- [Tailwind CSS](https://tailwindcss.com) - A utility-first CSS framework

---

<p align="center">Made with ❤️ by <a href="https://github.com/thanhtn2902">thanhtn2902</a></p>
