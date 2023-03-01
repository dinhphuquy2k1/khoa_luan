
<template>
    <div class="wrapper">
        <TheHeader />
        <TheContent />
        <TheNavbar />
    </div>


<!-- <div class="container">
        <nav class="navbar navbar-expand-sm bg-dark navbar-dark">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <router-link to="/test_setup" class="nav-link">Thiết lập đề</router-link>
                </li>
                <li class="nav-item">
                    <router-link to="/create" class="nav-link">Create Post</router-link>
                </li>
                <li class="nav-item">
                    <router-link to="/exam_selection" class="nav-link">Posts</router-link>
                </li>
                <li class="nav-item">
                    <router-link to="/create_excel_bank" class="nav-link">Tạo đề</router-link>
                </li>
            </ul>
        </nav><br />
        <router-view></router-view>
        </div> -->
</template>

<style>
@import url(../../public/css/main.css);
</style>

<script>
import TheContent from './components/Layout/TheContent.vue';
import TheHeader from './components/Layout/TheHeader.vue';
import TheNavbar from './components/Layout/TheNavbar.vue';
export default {
    components: {
        TheHeader,
        TheContent,
        TheNavbar
    }
}
</script>
