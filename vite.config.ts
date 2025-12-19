import {defineConfig} from 'vite';
import react from '@vitejs/plugin-react';
import path from 'path';

export default defineConfig({
    plugins: [react()],
    root: 'app/view/admin/ui',
    base: '/static-admin/build/',
    build: {
        outDir: '../../../../public/static-admin/build',
        emptyOutDir: true,
        manifest: true,
        rollupOptions: {
            // Input relative to root (app/view/admin/ui)
            input: path.resolve(__dirname, 'app/view/admin/ui/main.tsx')
        }
    },
    server: {
        host: '127.0.0.1',
        // For dev mode, we want to access via localhost:5173
        strictPort: true,
        cors: true
    },
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './app/view/admin/ui')
        }
    }
});
