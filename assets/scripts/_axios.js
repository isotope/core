import Axios from 'axios';
import '@bprogress/core/css'; // eslint-disable-line import/no-unresolved
import { BProgress } from '@bprogress/core';
import { extractScript } from './_dom';

const instance = Axios.create({
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
    },
});

instance.interceptors.request.use(
    (config) => {
        BProgress.start();
        return config;
    },
    (error) => {
        BProgress.done();
        return Promise.reject(error);
    },
);

// see https://gist.github.com/nemanja947/a71018e659f7d5255ea43c304f8ee108
instance.interceptors.response.use(
    (response) => {
        BProgress.done();
        return response;
    },
    (error) => {
        BProgress.done();
        return Promise.reject(error);
    },
);

export default instance;

export function IsotopeAction(response) {
    if (response.headers['content-type'] !== 'application/isotope-action+json') {
        return false;
    }

    const json = response.data;
    const event = new CustomEvent('isotope-action', { details: json, cancelable: true });

    window.dispatchEvent(event);

    if (event.defaultPrevented) {
        return true;
    }

    if (json.redirect) {
        window.location = json.redirect;

        return true;
    }

    if (json.message) {
        const [txt, scripts] = extractScript(json.message);

        document.body.insertAdjacentHTML('beforeend', txt);

        if (scripts) {
            const script = document.createElement('script');
            script.text = scripts;
            document.body.append(script);
        }

        return true;
    }

    return false;
}
