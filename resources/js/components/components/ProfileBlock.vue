<template>
<div @click="$emit('chat-selected', $props.user.user_id)" :to="'/thread/' + $props.user.user_id" :class="['profile-box', {'new-msg': $props.user.new}]">
    <h4>{{ $props.user.name }}</h4>
    <p>{{ $props.user.lastMessage }}</p>
    <span class="date">{{ $props.user.date }}</span>
</div>
</template>

<script>
export default {
    name: "ProfileBlock",
    props: {
        user: {
            type: Object
        }
    }
}
</script>

<style scoped lang="scss">
.profile-box {
    padding: 8px;
    position: relative;
    display: block;
    width: 100%;
    min-width: 310px;
    transition: all 0.3s ease;
    cursor: pointer;
    border-bottom: 1px solid #e3e3e3;

    h4 {
        font-weight: 500;
        display: block;
    }
    p {
        font-weight: 400;
        display: block;
    }
    .date {
        font-weight: 500;
        display: block;
        position: absolute;
        top: 8px;
        right: 8px;
    }
}
.profile-box:hover {
    text-decoration: none;
    background: #048cd6;
    color: #ffffff;

    p {
        color: #ffffff;
    }
}
.new-msg {
    background: #f1f1f1;
}
</style>
