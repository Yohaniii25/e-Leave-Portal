<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pradeshiya Sabha - Fixed Footer Demo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom gradient background */
        .footer-gradient {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 50%, #020617 100%);
        }
        
        /* Glassmorphism effect for cards */
        .glass-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        /* Smooth hover animations */
        .hover-glow:hover {
            text-shadow: 0 0 8px rgba(59, 130, 246, 0.6);
            transition: all 0.3s ease;
        }
        
        /* Social icon hover effects */
        .social-icon {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .social-icon:hover {
            transform: translateY(-2px) scale(1.1);
            filter: drop-shadow(0 4px 8px rgba(59, 130, 246, 0.4));
        }
    </style>
</head>
<body>
    

    <!-- Beautiful Fixed Footer -->
    <footer class="footer-gradient text-white relative overflow-hidden">
        <!-- Decorative background elements -->
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-0 left-0 w-64 h-64 bg-blue-500 rounded-full blur-3xl transform -translate-x-32 -translate-y-32"></div>
            <div class="absolute bottom-0 right-0 w-96 h-96 bg-purple-500 rounded-full blur-3xl transform translate-x-48 translate-y-48"></div>
        </div>
        
        <div class="relative z-10 py-12 px-6">
            <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-8">

                <!-- Company Info -->
                <div class="glass-card rounded-xl p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-gradient-to-r from-blue-400 to-purple-500 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent">
                            Pradeshiya Sabha
                        </h2>
                    </div>
                    <p class="text-sm text-gray-300 leading-relaxed">
                        Committed to public service and transparency. We strive to improve lives through innovation and dedication to our community.
                    </p>
                </div>

                <!-- Navigation -->
                <div class="glass-card rounded-xl p-6">
                    <h3 class="text-lg font-semibold mb-4 flex items-center">
                        <span class="w-2 h-2 bg-blue-400 rounded-full mr-2"></span>
                        Quick Links
                    </h3>
                    <ul class="space-y-3 text-sm text-gray-300">
                        <li><a href="#" class="hover-glow hover:text-blue-400 flex items-center transition-all duration-300">
                            <span class="w-1 h-1 bg-gray-400 rounded-full mr-2"></span>Home
                        </a></li>
                        <li><a href="#" class="hover-glow hover:text-blue-400 flex items-center transition-all duration-300">
                            <span class="w-1 h-1 bg-gray-400 rounded-full mr-2"></span>Leave Requests
                        </a></li>
                        <li><a href="#" class="hover-glow hover:text-blue-400 flex items-center transition-all duration-300">
                            <span class="w-1 h-1 bg-gray-400 rounded-full mr-2"></span>Manual Entry
                        </a></li>
                        <li><a href="#" class="hover-glow hover:text-blue-400 flex items-center transition-all duration-300">
                            <span class="w-1 h-1 bg-gray-400 rounded-full mr-2"></span>Admin Panel
                        </a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div class="glass-card rounded-xl p-6">
                    <h3 class="text-lg font-semibold mb-4 flex items-center">
                        <span class="w-2 h-2 bg-green-400 rounded-full mr-2"></span>
                        Contact Us
                    </h3>
                    <ul class="text-sm text-gray-300 space-y-3">
                        <li class="flex items-center group">
                            <svg class="w-4 h-4 mr-3 text-blue-400 group-hover:text-blue-300 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            support@pradeshiya.lk
                        </li>
                        <li class="flex items-center group">
                            <svg class="w-4 h-4 mr-3 text-green-400 group-hover:text-green-300 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            +94 71 123 4567
                        </li>
                        <li class="flex items-start group">
                            <svg class="w-4 h-4 mr-3 mt-0.5 text-purple-400 group-hover:text-purple-300 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Main Street, Colombo, Sri Lanka
                        </li>
                    </ul>
                </div>

                <!-- Social Media -->
                <div class="glass-card rounded-xl p-6">
                    <h3 class="text-lg font-semibold mb-4 flex items-center">
                        <span class="w-2 h-2 bg-pink-400 rounded-full mr-2"></span>
                        Follow Us
                    </h3>
                    <div class="flex space-x-4">
                        <a href="#" class="social-icon w-10 h-10 bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg flex items-center justify-center hover:from-blue-500 hover:to-blue-600" aria-label="Facebook">
                            <svg class="w-5 h-5 fill-current text-white" viewBox="0 0 24 24">
                                <path d="M22 12c0-5.522-4.477-10-10-10S2 6.478 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.988H7.898v-2.89h2.54v-2.199c0-2.507 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.465h-1.26c-1.243 0-1.63.771-1.63 1.562v1.867h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" />
                            </svg>
                        </a>
                        <a href="#" class="social-icon w-10 h-10 bg-gradient-to-r from-sky-400 to-sky-500 rounded-lg flex items-center justify-center hover:from-sky-300 hover:to-sky-400" aria-label="Twitter">
                            <svg class="w-5 h-5 fill-current text-white" viewBox="0 0 24 24">
                                <path d="M22.46 6c-.77.35-1.6.59-2.46.69a4.301 4.301 0 001.88-2.38 8.59 8.59 0 01-2.72 1.04 4.3 4.3 0 00-7.32 3.92 12.2 12.2 0 01-8.85-4.49 4.3 4.3 0 001.33 5.74 4.24 4.24 0 01-1.95-.54v.05a4.3 4.3 0 003.45 4.21 4.31 4.31 0 01-1.94.07 4.3 4.3 0 004.01 2.98A8.63 8.63 0 013 19.54a12.17 12.17 0 006.59 1.93c7.91 0 12.24-6.56 12.24-12.24l-.01-.56A8.79 8.79 0 0022.46 6z" />
                            </svg>
                        </a>
                        <a href="#" class="social-icon w-10 h-10 bg-gradient-to-r from-blue-700 to-blue-800 rounded-lg flex items-center justify-center hover:from-blue-600 hover:to-blue-700" aria-label="LinkedIn">
                            <svg class="w-5 h-5 fill-current text-white" viewBox="0 0 24 24">
                                <path d="M19 0h-14C2.239 0 1 1.239 1 2.75v18.5C1 22.761 2.239 24 4 24h14c1.761 0 3-1.239 3-2.75V2.75C22 1.239 20.761 0 19 0zM7.25 20.25H4.5v-9h2.75v9zM5.875 9.5C5.089 9.5 4.5 8.911 4.5 8.125S5.089 6.75 5.875 6.75 7.25 7.339 7.25 8.125 6.661 9.5 5.875 9.5zM20.25 20.25h-2.75v-4.75c0-1.106-.894-2-2-2s-2 .894-2 2v4.75h-2.75v-9h2.75v1.148c.622-.929 1.963-1.648 3.25-1.648 2.21 0 4 1.79 4 4v5.5z" />
                            </svg>
                        </a>
                    </div>
                    
                    <!-- Newsletter signup -->
                    <div class="mt-4">
                        <p class="text-xs text-gray-400 mb-2">Stay updated with our newsletter</p>
                        <div class="flex">
                            <input type="email" placeholder="Enter email" class="flex-1 px-3 py-2 text-xs bg-white/10 border border-white/20 rounded-l-lg focus:outline-none focus:border-blue-400 text-white placeholder-gray-400">
                            <button class="px-3 py-2 bg-gradient-to-r from-blue-500 to-purple-500 rounded-r-lg hover:from-blue-400 hover:to-purple-400 transition-all duration-300">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Bottom section -->
            <div class="mt-10 border-t border-white/20 pt-6">
                <div class="flex flex-col md:flex-row justify-between items-center text-sm text-gray-400">
                    <div class="mb-4 md:mb-0">
                        <p>&copy; 2024 Pradeshiya Sabha. All rights reserved.</p>
                    </div>
                    <div class="flex space-x-6">
                        <a href="#" class="hover:text-white transition-colors">Privacy Policy</a>
                        <a href="#" class="hover:text-white transition-colors">Terms of Service</a>
                        <a href="#" class="hover:text-white transition-colors">Accessibility</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>