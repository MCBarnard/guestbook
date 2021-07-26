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
                                    <input :disabled="nothingOn" v-model="responseMessage" name="message-input" type="text" class="form-control" placeholder="Enter something here..">
                                    <button :disabled="nothingOn" class="btn btn-outline-secondary" type="submit" id="button-addon2">
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
    created() {
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: '47b97d41f73ba8738cc5',
            cluster: 'ap2',
            useTLS: true
        })

        window.Echo.channel('messages')
            .listen('MessagesUpdated', (e) => {
                this.echoReceived(e);
            });
    },
    async mounted() {
        this.nothingOn = false;
        this.fetching = true;
        await axios.get("/home/user").then(response => {
            this.activeId = response.data.id;
            this.username = response.data.name;
            this.isAdmin = parseInt(response.data.is_admin);
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
        async echoReceived(echo) {
            if (echo.message.user_id === this.activeId) {
                // if we have an open chat, and the active chat is the echoed chat and it isnt a delete broadcast push to chat
                if (typeof this.activeData !== "undefined" && echo.message.action === "new") {
                    this.activeData.messages.push({
                        id: echo.message.message_id,
                        admin: echo.message.from_admin,
                        message: echo.message.message,
                        new: false,
                        timestamp: echo.message.created_at,
                        user_id: echo.message.user_id
                    });
                    this.scrollDown();
                } else if (echo.message.action === "delete") {
                    for (let i = 0; i < this.activeData.messages.length; i++) {
                        const messageIsCorrectMessage_ID = parseInt(this.activeData.messages[i].message_id) === parseInt(echo.message.message_id)
                        const messageIsCorrectID = parseInt(this.activeData.messages[i].id) === parseInt(echo.message.message_id)
                        if (messageIsCorrectMessage_ID || messageIsCorrectID) {
                            this.activeData.messages.splice(i, 1);
                            i = this.activeData.messages.length;
                        }
                    }
                    this.scrollDown();
                } else if (typeof this.activeData !== "undefined" && echo.message.action === "edit") {
                    if (typeof this.activeData !== "undefined" && this.activeData.messages.length !== 0) {
                        for (let x = 0; x < this.activeData.messages.length; x++) {
                            if (parseInt(this.activeData.messages[x].message_id) === parseInt(echo.message.message_id)) {
                                this.activeData.messages[x].message = echo.message.message;
                            }
                        }
                    }
                    this.scrollDown();
                }
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
