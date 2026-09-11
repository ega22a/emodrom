import { defineConfig } from 'vite';

export default defineConfig({
    test: {
        environment: 'jsdom',
        setupFiles: ['./resources/js/testing/setup.js'],
        include: ['resources/js/**/*.test.js'],
    },
});
