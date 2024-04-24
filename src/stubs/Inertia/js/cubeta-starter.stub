import './bootstrap';
import '../css/cubeta-starter.css';
import {createRoot} from 'react-dom/client';
import {createInertiaApp} from '@inertiajs/react';
import Layout from "@/Components/layouts/Layout";

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => {
        const pages = import.meta.glob("./Pages/**/*.tsx", {eager: true});
        let page = pages[`./Pages/${name}.tsx`];
        // @ts-ignore
        page.default.layout = page.default.layout || ((page) => <Layout children={page}/>);
        return page;
    },
    setup({el, App, props}) {
        const root = createRoot(el);

        root.render(<App {...props} />);
    },
    progress: {
        color: '#4B5563',
    },
});
