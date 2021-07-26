<template>
    <div class="message">
        <div class="message__inner" :class="{'admin': $props.message.admin}">
            <img v-if="$props.message.admin" src="https://www.payfast.co.za/assets/images/payfast.ico" alt="">
            <p class="body">
                {{ $props.message.message }}
            </p>
            <div class="timestamp">
                <div v-if="$props.message.admin && $props.admin" class="crud-box">
                    <button @click="handleEdit()" class="crud-action"><img src="/images/pencil-yellow.svg" alt=""></button>
                    <button @click="handleDelete()" class="crud-action spacing-image"><img src="/images/trash-red.svg" alt=""></button>
                </div>
                {{ $props.message.timestamp }}
                <div v-if="!$props.message.admin && !$props.admin" class="crud-box">
                    <button @click="handleEdit()" class="crud-action spacing-image"><img src="/images/pencil-yellow.svg" alt=""></button>
                    <button @click="handleDelete()" class="crud-action"><img src="/images/trash-red.svg" alt=""></button>
                </div>
            </div>
        </div>
        <div class="edit-form-modal" :class="{'show': showEdit}">
            <div class="edit-form-modal__inner">
                <button @click="closeEditModal()" class="edit-form-modal__inner__exit">
                    <img src="/images/x-circle-red.svg" alt="">
                </button>
                <h3>Edit Message</h3>
                <p>Feel free to edit your messages as much as you want, there's no limit...</p>
                <form action="#" @submit.prevent="editMessage()">
                    <div class="input-group">
                        <textarea :disabled="sending" v-model="newMessage" name="message-input" type="text" class="form-control" placeholder="Enter something here.."></textarea>
                        <button :disabled="sending" class="btn btn-outline-secondary" type="submit" id="button-addon2">
                            <div v-if="sending" class="spinner-border text-secondary" role="status"></div>
                            <img v-else src="/images/paper-airplane.svg" alt="">
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="delete-form-modal" :class="{'show': showDelete}">
            <div class="edit-form-modal__inner">
                <button @click="closeDeleteModal()" class="delete-form-modal__inner__exit">
                    <img src="/images/x-circle-red.svg" alt="">
                </button>
                <h3>Delete Message</h3>
                <p>Delete <b>"{{ $props.message.message }}"</b> ?</p>
                <p>Are you sure? This cannot be undone...</p>
                <form action="#" @submit.prevent="deleteMessage()">
                    <div class="input-group">
                        <button :disabled="sending" class="btn btn-danger w-100" type="submit" id="button-addon3">
                            <div v-if="sending">
                                <div class="spinner-border text-light" role="status"></div>
                            </div>
                            <div v-else>
                                Yes, I am sure! <img src="/images/trash-white.svg" alt="">
                            </div>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "MessageBlock",
    data() {
        return {
            sending: false,
            showEdit: false,
            newMessage: this.$props.message.message,
            showDelete: false
        }
    },
    props: {
        message: {
            type: Object
        },
        admin: {
            type: Number
        }
    },
    methods: {
        handleDelete() {
            this.showDelete = true
        },
        handleEdit() {
            this.showEdit = true
        },
        closeEditModal() {
            this.showEdit = false
        },
        closeDeleteModal() {
            this.showDelete = false
        },
        editMessage() {
            this.sending = true;
            const path = this.$props.message.admin ? 'admin/messages' : 'home';
            const data = {
                message: this.newMessage
            };
            const id = !this.$props.message.message_id ? this.$props.message.id : this.$props.message.message_id;
            axios.put(`/${path}/${id}/edit`, data)
            .then(response => {
                if (response.status === 200) {
                    this.sending = false;
                    this.closeEditModal();
                }
            });
        },
        deleteMessage() {
            this.sending = true;
            const path = this.$props.admin ? 'admin/messages' : 'home';
            const data = {
                message: this.newMessage
            };
            const id = !this.$props.message.message_id ? this.$props.message.id : this.$props.message.message_id;
            axios.get(`/${path}/${id}/delete`, data)
                .then(response => {
                    this.sending = false;
                    if (response.status === 200) {
                        this.closeDeleteModal();
                    }
                });
        }
    }
}
</script>

<style scoped lang="scss">
.message {
    display: block;
    padding: 4px 8px 16px;
    width: 100%;
    margin-bottom: 12px;

    &__inner {
        width: 100%;
        display: flex;
        justify-content: flex-end;
        position: relative;

        .body {
            background: #09a8ff;
            padding: 10px;
            color: #ffffff;
        }

        .timestamp {
            position: absolute;
            left: unset;
            right: 0;
            bottom: -22px;
            display: flex;
        }

        &.admin {
            justify-content: flex-start;

            .body {
                background: #e5e5e5;
                color: #2d2d2d;
            }

            img {
                max-width: 40px;
                max-height: 40px;
                border-radius: 50%;
                margin-right: 8px;
            }

            .timestamp {
                position: absolute;
                left: 0;
                right: unset;
                bottom: -22px;
                display: flex;
            }
        }
    }
}
.crud-box {
    display: flex;
    justify-content: space-evenly;
}
.crud-action {
    background: #ffffff;
    border: none;
    outline: none;
    display: block;
    -webkit-box-shadow: 0 0 0 1px #969191;
    -moz-box-shadow: 0 0 0 1px #969191;
    box-shadow: 0 0 0 1px #969191;
    border-radius: 50%;

    &:hover {
        background: #f1f1f1;
    }

    &:focus {
        outline: none !important;
        border: none!important;
    }

    button {
        transform: translateY(-4px);
    }
    img {
        height: 15px;
        margin: 0!important;
    }
}
.spacing-image {
    margin: 0 4px;
}
.edit-form-modal {
    display: none;
    background: rgba(0, 0, 0, 0.5);
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100vh;
    z-index: 9;
    pointer-events: none;
    opacity: 0;

    &__inner {
        background: #ffffff;
        width: 100%;
        margin: auto;
        max-width: 400px;
        padding: 20px;
        border-radius: 5px;
        position: relative;

        &__exit {
            position: absolute;
            top: 10px;
            right: 10px;
            background: none;
            border: none!important;
            outline: none!important;

            img {
                width: 25px;
            }
        }
    }
}
.edit-form-modal.show {
    display: flex;
    opacity: 1;
    pointer-events: auto;
    justify-content: center;
    flex-direction: column;

    form {
        margin-top: 16px;

        button img {
            transform: rotate(-210deg);
        }
    }
}
.delete-form-modal {
    display: none;
    background: rgba(0, 0, 0, 0.5);
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100vh;
    z-index: 9;
    pointer-events: none;
    opacity: 0;

    &__inner {
        background: #ffffff;
        width: 100%;
        margin: auto;
        max-width: 400px;
        padding: 20px;
        border-radius: 5px;
        position: relative;

        &__exit {
            position: absolute;
            top: 10px;
            right: 10px;
            background: none;
            border: none!important;
            outline: none!important;

            img {
                width: 25px;
            }
        }
    }
}
.delete-form-modal.show {
    display: flex;
    opacity: 1;
    pointer-events: auto;
    justify-content: center;
    flex-direction: column;

    form {
        margin-top: 16px;
    }
}
</style>
