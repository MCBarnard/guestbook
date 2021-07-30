<template>
    <div>
        <div class="chat_container">
            <div class="chat_container__inner">
                <div class="chat_container__inner__top">
                    <h3><b>Guestbook for PayFast</b></h3>
                    <h3><b>{{ username }}</b></h3>
                </div>
                <div class="chat_container__inner__bottom">
                    <div class="chat_container__inner__bottom__right">
                        <!--         USER MESSAGES               -->
                        <div class="chat_container__inner__bottom__right__top__wrapper">
                            <div id="message-box-wrapper" class="chat_container__inner__bottom__right__top">
                                <div id="message-box" class="chat_container__inner__bottom__right__top__message-box" v-if="!showSpinner && activeData">
                                    <MessageBlock v-for="(message, index) in activeData.messages" :admin="isAdmin" :message="message" :key="index" />
                                </div>
                                <div class="chat_container__inner__bottom__right__top__spinner" v-else-if="nothingOn">
                                    <div>
                                        <img src="https://uploads-ssl.webflow.com/5edfc79600691067acf835bd/5f2964dc6a17b533b1346c54_PayFast-Logo-Colour-2.png" alt="">
                                    </div>
                                </div>
                                <div class="chat_container__inner__bottom__right__top__spinner" v-else>
                                    <div class="spinner-border" role="status">
                                        <span class="visually-hidden"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--         SEND MESSAGE BOX               -->
                        <div class="chat_container__inner__bottom__right__bottom">
                            <form action="#" @submit.prevent="sendMessage(activeUserChat)">
                                <div class="input-group">
                                    <input :disabled="nothingOn || showSpinner" v-model="responseMessage" name="message-input" type="text" class="form-control" placeholder="Enter something here..">
                                    <button :disabled="nothingOn || showSpinner" class="btn btn-outline-secondary" type="submit" id="button-addon2">
                                        <img src="/images/paper-airplane.svg" alt="">
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import ProfileBlock from "../components/ProfileBlock";
import MessageBlock from "../components/MessageBlock";
import Echo from "laravel-echo";

export default {
    name: "HomePage",
    components: {
        ProfileBlock,
        MessageBlock
    },
    computed: {
        showSpinner() {
            return this.fetching;
        },
        activeUserChat() {
            return this.activeData?.activeId;
        }
    },
    data() {
        return {
            threads: {},
            fetching: false,
            activeData: undefined,
            nothingOn: true,
            responseMessage: "",
            username: "",
            userId: "",
            isAdmin: false
        }
    },
    async mounted() {
        this.nothingOn = false;
        this.fetching = true;
        await axios.get("/home/user").then(response => {
            this.activeId = response.data.id;
            this.username = response.data.name;
            this.isAdmin = parseInt(response.data.is_admin);
            window.Echo = new Echo({
                broadcaster: 'pusher',
                key: '47b97d41f73ba8738cc5',
                cluster: 'ap2',
                useTLS: true,
                csrfToken: window.options.csrfToken
            })

            window.Echo.private('messages.'+ response.data.id)
                .listen('MessagesUpdated', (e) => {
                    this.echoReceived(e);
                });
        });
        await this.fetchChat();
    },
    methods: {
        async fetchChat() {
            this.nothingOn = false;
            this.fetching = true;
            this.responseMessage = "";
            await axios.get(`/home`).then(response => {
                this.activeData = {
                    messages: response.data,
                }
                this.activeData.messages = response.data;
                this.fetching = false;

                setTimeout(() => {
                    const wrapper = document.getElementById("message-box-wrapper")
                    const div = document.getElementById("message-box")
                    wrapper.scrollTop = div.clientHeight
                }, 300);
            });
        },
        async sendMessage() {
            this.fetching = true;
            const data = {
                "message": this.responseMessage
            }
            await axios.post(`/home/new`, data).then(response => {
                if (response.status === 200) {
                    this.fetching = false;
                    this.responseMessage = "";
                }
            });
        },
        handleNewMessage(message) {
            this.activeData.messages.push({
                id: message.message_id,
                admin: message.from_admin,
                message: message.message,
                new: false,
                timestamp: message.created_at,
                user_id: message.user_id
            });
            this.scrollDown();
        },
        async handleEditMessage(message) {
            if (typeof this.activeData !== "undefined" && this.activeData.messages.length !== 0) {
                this.activeData.messages.forEach(item => {
                    if (parseInt(item.message_id) === parseInt(message.message_id)) {
                        item.message = message.message;
                    }
                });
            }
        },
        async handleDeleteMessage(message) {
            if (typeof this.activeData !== "undefined" && this.activeData.messages.length !== 0) {
                let indexToSplice;
                this.activeData.messages.forEach((item, index) => {
                    if (parseInt(item.message_id) === parseInt(message.message_id)) {
                        indexToSplice = index;
                    }
                });
                this.activeData.messages.splice(indexToSplice, 1);
            }
        },
        async echoReceived(echo) {
            // Sanity check, this was to prevent users from reading other user's messages
            // when we were broadcasting to a public channel but we now broadcast on private channels
            if (echo.user_id === this.activeId) {
                switch (echo.action) {
                    case "new":
                        this.handleNewMessage(echo);
                        break;
                    case "edit":
                        this.handleEditMessage(echo);
                        break;
                    case "delete":
                        this.handleDeleteMessage(echo);
                        break;
                }
                // if we have an open chat, and the active chat is the echoed chat and it isnt a delete broadcast push to chat
                // if (typeof this.activeData !== "undefined" && echo.action === "new") {
                //     this.activeData.messages.push({
                //         id: echo.message_id,
                //         admin: echo.from_admin,
                //         message: echo.message,
                //         new: false,
                //         timestamp: echo.created_at,
                //         user_id: echo.user_id
                //     });
                //     this.scrollDown();
                // } else if (echo.action === "delete") {
                //     for (let i = 0; i < this.activeData.messages.length; i++) {
                //         const messageIsCorrectMessage_ID = parseInt(this.activeData.messages[i].message_id) === parseInt(echo.message_id)
                //         const messageIsCorrectID = parseInt(this.activeData.messages[i].id) === parseInt(echo.message_id)
                //         if (messageIsCorrectMessage_ID || messageIsCorrectID) {
                //             this.activeData.messages.splice(i, 1);
                //             i = this.activeData.messages.length;
                //         }
                //     }
                //     this.scrollDown();
                // } else if (typeof this.activeData !== "undefined" && echo.action === "edit") {
                //     if (typeof this.activeData !== "undefined" && this.activeData.messages.length !== 0) {
                //         for (let x = 0; x < this.activeData.messages.length; x++) {
                //             if (parseInt(this.activeData.messages[x].message_id) === parseInt(echo.message_id)) {
                //                 this.activeData.messages[x].message = echo.message;
                //             }
                //         }
                //     }
                //     this.scrollDown();
                // }
            }
        },
        scrollDown() {
            setTimeout(() => {
                const wrapper = document.getElementById("message-box-wrapper")
                const div = document.getElementById("message-box")
                wrapper.scrollTop = div.clientHeight
            }, 300);
        },
    }
}
</script>

<style scoped lang="scss">
@import "./resources/sass/components/text-block";
</style>
