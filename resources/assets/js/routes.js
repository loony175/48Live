import Home from './components/Home';
import FlvJs from './components/FlvJs';
import VideoJs from './components/VideoJs';

const routes = [
    {
        path: '/',
        components: {
            default: Home
        }
    },
    {
        path: '/flvjs/:liveId',
        components: {
            default: FlvJs
        }
    },
    {
        path: '/videojs/:liveId',
        components: {
            default: VideoJs
        }
    }
];

export default routes;
