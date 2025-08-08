import Axios from 'axios';
import NProgress from 'nprogress';
import 'nprogress/nprogress.css'

const instance = Axios.create({
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
    },
});

instance.interceptors.request.use(config => {
    NProgress.start();
    return config;
}, error => {
    NProgress.done();
    return Promise.reject(error);
});

// see https://gist.github.com/nemanja947/a71018e659f7d5255ea43c304f8ee108
instance.interceptors.response.use(response => {
    NProgress.done();
    return response;
}, error => {
    NProgress.done();
    return Promise.reject(error);
});

instance.interceptors.response.use(response => {
    if ('application/isotope-action+json' !== response.headers['content-type']) {
        return response;
    }

    const json = response.data;
    const event = new CustomEvent('isotope-action', { details: json, cancelable: true });

    window.dispatchEvent(event)

    if (event.defaultPrevented) {
        return response;
    }

    if (json.redirect) {
        window.location = json.redirect;
    } else if (json.message) {
        alert(json.message);
    }

    return response;
});

export default instance;
