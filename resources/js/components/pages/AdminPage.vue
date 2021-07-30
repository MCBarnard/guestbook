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
                        <ProfileBlock @chat-selected="fetchChat(user.user_id)" :newMessage="user.new" v-for="(user, index) in activeThreads" :user="user" :key="index" />
                    </div>
                </div>
                <div class="chat_container__inner__bottom__right">
                    <!--         USER MESSAGES               -->
                    <div class="chat_container__inner__bottom__right__top__wrapper">
                        <div id="message-box-wrapper" class="chat_container__inner__bottom__right__top">
                            <div id="message-box" class="chat_container__inner__bottom__right__top__message-box" v-if="!showSpinner && activeData">
                                <MessageBlock v-for="(message, index) in activeData.messages" :admin="1" :message="message" :key="index" />
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
        pushToThreads(message) {
            this.threads.push({
                id: message.message_id,
                date: message.created_at,
                email: message.email,
                lastMessage: message.message,
                name: message.name,
                new: !message.opened,
                user_id: message.user_id
            });
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
        handleNewMessage(message) {
            // Push to side menu
            if (this.threads.length > 0) {
                let found = false;
                this.threads.forEach(item => {
                    if (!message.from_admin && item.user_id === message.user_id) {
                        found = true;
                        item.date = message.created_at;
                        item.new = typeof this.activeData === "undefined" || this.activeData.activeId !== message.user_id;
                        item.lastMessage = message.message;
                    }
                });
                if (!found && !message.from_admin) {
                    this.pushToThreads(message);
                }
            } else {
                this.pushToThreads(message);
            }
            // Push to view
            if (typeof this.activeData !== "undefined" && this.activeData.activeId === message.user_id) {
                this.activeData.messages.push({
                    id: message.message_id,
                    admin: message.from_admin,
                    message: message.message,
                    new: false,
                    timestamp: message.created_at,
                    user_id: message.user_id
                });
                this.scrollDown();
            }
        },
        async handleEditMessage(message) {
            if (typeof this.activeData !== "undefined" && this.activeData.messages.length !== 0) {
                this.activeData.messages.forEach(item => {
                    if (parseInt(item.message_id) === parseInt(message.message_id)) {
                        item.message = message.message;
                    }
                });
            }
            let itemIndex;
            this.threads.forEach((item, index) => {
                if (parseInt(item.user_id) === parseInt(message.user_id)) {
                    itemIndex = index;
                }
            });
            await axios.get(`/admin/messages/${message.user_id}?set_opened=0`).then(response => {
                if (response.data.length > 0) {
                    const lastItem = response.data[response.data.length - 1];
                    this.threads[itemIndex].id = lastItem.message_id;
                    this.threads[itemIndex].date = lastItem.timestamp;
                    this.threads[itemIndex].email = lastItem.email;
                    this.threads[itemIndex].lastMessage = lastItem.message;
                    this.threads[itemIndex].new = lastItem.new;
                }
            });

        },
        async handleDeleteMessage(message) {
            const activeDataNotUndefined = typeof this.activeData !== "undefined";
            const hasMessages = activeDataNotUndefined ? this.activeData.messages.length > 1 : false;
            const viewingCurrentUserMessages = activeDataNotUndefined ? this.activeData.activeId === message.user_id : false;

            if (activeDataNotUndefined && hasMessages && viewingCurrentUserMessages) {
                let indexToSplice;
                this.activeData.messages.forEach((item, index) => {
                    if (parseInt(item.message_id) === parseInt(message.message_id)) {
                        indexToSplice = index;
                    }
                });
                this.activeData.messages.splice(indexToSplice, 1);
            } else {
                let itemIndex;
                this.threads.forEach((item, index) => {
                    if (parseInt(item.user_id) === parseInt(message.user_id)) {
                        itemIndex = index;
                    }
                });
                await axios.get(`/admin/messages/${message.user_id}?set_opened=0`).then(response => {
                    if (response.data.length === 0) {
                        this.threads.splice(itemIndex, 1);
                        this.activeData = undefined;
                        this.nothingOn = true
                    } else if (response.data.length > 0) {
                        const lastItem = response.data[response.data.length - 1];
                        this.threads[itemIndex].id = lastItem.message_id;
                        this.threads[itemIndex].date = lastItem.timestamp;
                        this.threads[itemIndex].email = lastItem.email;
                        this.threads[itemIndex].lastMessage = lastItem.message;
                        this.threads[itemIndex].new = lastItem.new;
                    }
                });
            }
        },
        async echoReceived(echo) {
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
        }
    }
}
</script>

<style scoped lang="scss">
@import "./resources/sass/components/text-block";
</style>
