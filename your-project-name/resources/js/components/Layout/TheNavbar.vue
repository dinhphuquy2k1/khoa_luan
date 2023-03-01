<template>
    <div class="m-navbar">
        <div class="container">
            <nav class="navbar navbar-expand-sm bg-dark navbar-dark">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <router-link to="/" class="nav-link">Thiết lập đề</router-link>
                    </li>
                    <li class="nav-item">
                        <router-link to="/create" class="nav-link">Tạo kì thi</router-link>
                    </li>
                    <li class="nav-item">
                        <router-link to="/create_exam_session" class="nav-link">Tạo ca thi</router-link>
                    </li>
                    <li class="nav-item">
                        <router-link to="/exam_selection" class="nav-link">Chọn đề cho ca thi</router-link>
                    </li>
                    <li class="nav-item">
                        <router-link to="/create_excel_bank" class="nav-link">Tạo đề</router-link>
                    </li>
                    <li class="nav-item">
                        <router-link to="/upload" class="nav-link">Upload bài làm</router-link>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</template>

<script>
export default {
}
</script>
