<template>
<div>
    <div class="chat_container">
        <div class="chat_container__inner">
            <div class="chat_container__inner__top">
                <h3><b>Guestbook for PayFast</b></h3>
                <h3><b>ADMIN</b></h3>
            </div>
            <div class="chat_container__inner__bottom">
                <div class="chat_container__inner__bottom__left">
                    <!--         USER PROFILES               -->
                    <div class="chat_container__inner__bottom__left__bottom">
                        <ProfileBlock @chat-selected="fetchChat(user.user_id)" :newMessage="user.new" v-for="(user, index) in activeThreads" :user="user" :key="user.user_id" />
                    </div>
                </div>
                <div class="chat_container__inner__bottom__right">
                    <!--         USER MESSAGES               -->
                    <div class="chat_container__inner__bottom__right__top__wrapper">
                        <div id="message-box-wrapper" class="chat_container__inner__bottom__right__top">
                            <div id="message-box" class="chat_container__inner__bottom__right__top__message-box" v-if="!showSpinner && activeData">
                                <MessageBlock v-for="(message, index) in activeData.messages" :admin="message.admin" :message="message" :key="index" />
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
    name: "AdminPage",
    components: {
        ProfileBlock,
        MessageBlock
    },
    computed: {
        showSpinner() {
            return this.fetching || typeof this.activeData === "undefined";
        },
        activeUserChat() {
            return this.activeData?.activeId;
        },
        activeThreads() {
            return this.threads;
        }
    },
    data() {
        return {
            threads: [],
            fetching: false,
            activeData: undefined,
            nothingOn: true,
            responseMessage: ""
        }
    },
    created() {
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: '47b97d41f73ba8738cc5',
            cluster: 'ap2',
            useTLS: true,
            csrfToken: window.options.csrfToken
        })

        window.Echo.private('messages.admin')
            .listen('MessagesUpdated', e => {
                this.echoReceived(e);
            });
    },
    async mounted() {
        await this.fetchUserThreads();
    },
    methods: {
        async fetchUserThreads() {
            this.nothingOn = true;
            await axios.get("/admin/messages/all").then(response => {
                if (typeof response.data === "object") {
                    this.threads = response.data;
                }
            });
        },
        async fetchChat(id) {
            this.nothingOn = false;
            this.fetching = true;
            this.activeData = {
                activeId: id
            }
            this.responseMessage = "";
            await axios.get(`/admin/messages/${id}`).then(response => {
                this.activeData = {
                    messages: response.data,
                    activeId: id
                }
                this.activeData.messages = response.data;
                this.fetching = false;
                this.scrollDown();

                this.threads.forEach(item => {
                    if (item.user_id === id) {
                        item.new = false;
                    }
                })
            });
        },
        scrollDown() {
            setTimeout(() => {
                const wrapper = document.getElementById("message-box-wrapper")
                const div = document.getElementById("message-box")
                wrapper.scrollTop = div.clientHeight
            }, 300);
        },
        async sendMessage(id) {
            this.fetching = true;
            const data = {
                "message": this.responseMessage
            }
            await axios.post(`/admin/messages/${id}`, data).then(response => {
                if (response.status === 200) {
                    this.fetching = false;
                    this.responseMessage = "";
                }
            });
        },
        async echoReceived(echo) {
            if (this.threads.length > 0) {
                for (let i = 0; i < this.threads.length; i++) {
                    // handle all edit
                    if (echo.message.action === "edit") {
                        if (typeof this.activeData !== "undefined" && this.activeData.messages.length !== 0) {
                            for (let x = 0; x < this.activeData.messages.length; x++) {
                                if (parseInt(this.activeData.messages[x].message_id) === parseInt(echo.message.message_id)) {
                                    this.activeData.messages[x].message = echo.message.message;
                                }
                            }
                        }
                        // if user_id in first nav matches echo user_id and it wasnt a delete
                    } else if (this.threads[i].user_id === echo.message.user_id && echo.message.action === "new") {
                        if (!echo.message.from_admin) {
                            this.threads[i].date = echo.message.created_at;
                            this.threads[i].new = typeof this.activeData === "undefined" || this.activeData.activeId !== echo.message.user_id;
                            this.threads[i].lastMessage = echo.message.message;
                        }
                        i = this.threads.length;
                    } else if (i === this.threads.length - 1 && echo.message.action === "new") {
                        this.threads.push({
                            id: echo.message.message_id,
                            date: echo.message.created_at,
                            email: echo.message.email,
                            lastMessage: echo.message.message,
                            name: echo.message.name,
                            new: !echo.message.opened,
                            user_id: echo.message.user_id
                        });
                        // if it is delete and the current nav has the same message_id as echoed id
                    } else if (echo.message.action === "delete" && this.threads[i].user_id === echo.message.user_id) {
                        if (typeof this.activeData !== "undefined" && this.activeData.messages.length > 1) {
                            for (let x = 0; x < this.activeData.messages.length; x++) {
                                if (parseInt(this.activeData.messages[x].message_id) === parseInt(echo.message.message_id)) {
                                    this.activeData.messages.splice(x, 1);
                                }
                            }
                        } else {
                            this.threads.splice(i, 1);
                            this.activeData = undefined;
                            this.nothingOn = true
                        }
                    }
                }
            } else if (echo.message.action = "new") {
                this.threads.push({
                    id: echo.message.message_id,
                    date: echo.message.created_at,
                    email: echo.message.email,
                    lastMessage: echo.message.message,
                    name: echo.message.name,
                    new: !echo.message.opened,
                    user_id: echo.message.user_id
                });
            }
            // if we have an open chat, and the active chat is the echoed chat and it isnt a delete broadcast push to chat
            if (typeof this.activeData !== "undefined" && this.activeData.activeId === echo.message.user_id && echo.message.action === "new") {
                this.activeData.messages.push({
                    id: echo.message.message_id,
                    admin: echo.message.from_admin,
                    message: echo.message.message,
                    new: false,
                    timestamp: echo.message.created_at,
                    user_id: echo.message.user_id
                });
                this.scrollDown();
            }
        }
    }
}
</script>

<style scoped lang="scss">
@import "./resources/sass/components/text-block";
</style>
