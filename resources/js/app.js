import Vue from "vue";
import router from "./router"
import App from "./components/App";
import Vuelidate from "vuelidate";
import Echo from "laravel-echo";
window.Pusher = require('pusher-js');

Vue.use(Vuelidate)
require('./bootstrap');

const app = new Vue({
    el: '#app',
    components: {
        App
    },
    router,
    // created(){
    //     window.Echo = new Echo({
    //         broadcaster: 'pusher',
    //         key: '47b97d41f73ba8738cc5',
    //         cluster: 'ap2',
    //         useTLS: true
    //     })
    //
    //     window.Echo.channel('messages')
    //         .listen('MessagesUpdated', (e) => {
    //             console.log(e);
    //         });
    // }
});
