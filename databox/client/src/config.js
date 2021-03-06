import store from './store';

const configData = window.config;

class Config {
    get(key) {
        return store.get(key) || configData[key];
    }

    all() {
        return configData;
    }

    set(key, value) {
        store.set(key, value);
    }

    isDirectLoginForm() {
        return this.get('directLoginForm') ? this.get('directLoginForm') === 'true' : false;
    }

    setDirectLoginForm(directLoginForm) {
        return this.set('directLoginForm', directLoginForm ? 'true' : 'false');
    }

    getBaseURL() {
        return this.get('baseUrl');
    }

    getAvailableLocales() {
        return configData.locales;
    }

    getSignUpURL() {
        return `${this.getAuthBaseUrl()}/en/register`;
    }

    getAuthBaseUrl() {
        return this.get('authBaseUrl');
    }

    getClientCredential() {
        return {
            clientId: this.get('clientId'),
            clientSecret: this.get('clientSecret'),
        };
    }

    setClientCredential({clientId, clientSecret}) {
        this.set('clientId', clientId);
        this.set('clientSecret', clientSecret);
    }

    setUploadBaseURL(url) {
        this.set('baseUrl', url);
    }

    setAuthBaseURL(url) {
        this.set('authBaseUrl', url);
    }

    devModeEnabled() {
        return configData.devMode;
    }
}

const config = new Config();

export default config;
