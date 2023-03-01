<template>
    <div>
        <div v-if="!examSelected" class="examselection">
            form chọn đề chấm
            <div v-for="exam in listExam">
                <div @click="getExamSession(exam)" class="itemExam">Kì thi {{ exam['ten_ki_thi'] }}</div>
            </div>
        </div>

        <div class="examSession">
            <div v-if="!examSessionSelected" v-for="examSession in listExamSession">
                <div class="itemExam" @click="getAssessment(examSession)">Mã ca thi {{ examSession['ma_ca_thi'] }}</div>
            </div>
        </div>

        <div class="assessment" v-if="!selectedAssessment && examSessionSelected">

            <div v-for="assessment in listAssessment">
                <div class="itemExam" @click="setupAssessment(assessment)">Mã đề {{ assessment['excel_bank_name'] }}</div>
            </div>

        </div>
        <div class="" v-if="selectedAssessment">
            <input type="file" @change="select" ref="file">
            <button @click="formSubmit">Upload</button>

        </div>

        {{ uploaded }}


        <a href="/extractExam">Giải nén</a>

        <!-- <form action="/uploadFileExam" method="post">

            <input type="file" name="file">
            <input type="submit" value="Up">
        </form> -->

    </div>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            listExam: [],   //danh sách kì thi
            listExamSession: [], //danh sách ca thi
            listAssessment: [],    //danh sách đề
            type: 0,
            examSelected: null,
            examSessionSelected: null,
            selectedAssessment: null, //mảng lưu các danh sách đề được chọn

            param: [],

            fileUpload: null,

            file: null,
            chunks: [],
            uploaded: 0
        }
    },
    watch: {
        chunks(n, o) {
            if (n.length > 0) {
                this.upload();
            }
        }
    },
    computed: {
        progress() {
            return Math.floor((this.uploaded * 100) / this.file.size);
        },
        formData() {
            let formData = new FormData;

            formData.set('is_last', this.chunks.length === 1);
            formData.set('file', this.chunks[0], `${this.file.name}.part`);

            return formData;
        },
        config() {
            return {
                method: 'POST',
                data: this.formData,
                url: '/uploadFileExam',
                headers: {
                    'Content-Type': 'application/octet-stream'
                },
                onUploadProgress: event => {
                    this.uploaded += event.loaded;
                }
            };
        }
    },
    methods: {
        /**
         * Lấy danh sách các ca thi
         * **/
        async getExamSession(exam) {
            this.examSelected = exam;
            await axios.post(`/getExamSessionInfos/${exam['id']}`).then(res => {
                this.listExamSession = res.data;
            }).catch(e => {
                console.log(e);
            })
        },

        select(event) {
            this.file = event.target.files.item(0);
            this.createChunks();
        },
        upload() {
            axios(this.config).then(response => {
                this.chunks.shift();
            }).catch(error => { });
        },
        createChunks() {
            let size = 4096, chunks = Math.ceil(this.file.size / size);

            for (let i = 0; i < chunks; i++) {
                this.chunks.push(this.file.slice(
                    i * size, Math.min(i * size + size, this.file.size), this.file.type
                ));
            }
        },

        /**
         * Chọn file upload
         * **/
        onFileUpload(e) {

            this.fileUpload = this.$refs.file.files[0];
            let formData = new FormData();
            // console.log(this.fileUpload);
            formData.append('fileImport', this.fileUpload);
            this.axios.post("/uploadFileExam", this.fileUpload).then(res => {
                console.log(res);
            }).catch(error => {
                console.log(error);
            })
            // let reader = new FileReader();
            // let vm = this;
            // reader.onload = (e) => {
            //     vm.fileUpload = e.target.result;
            // };
            // reader.readAsDataURL(file);
        },
        formSubmit(e) {
            e.preventDefault();
            const config = {
                headers: { 'content-type': 'multipart/form-data' }
            }
            let formData = new FormData();
            // console.log(this.fileUpload);
            formData.append('fileImport', this.fileUpload);
            this.axios.post("/uploadFileExam", formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            }).then(res => {
                console.log(res);
            }).catch(error => {
                console.log(error);
            })
        },

        /**
         * Lấy danh sách đề thi
         * **/
        async getAssessment(assessment) {
            this.examSessionSelected = assessment;
            await axios.post('/getExamByExamSessionId', assessment).then(res => {
                console.log(res);
                this.listAssessment = res.data;

            }).catch(e => {
                console.log(e);
            })
        },

        /**
         * Chọn đề để upload file bài làm
         * **/
        setupAssessment(assessment) {
            this.selectedAssessment = assessment;
        }
    },
    created() {
        axios.post('/listExam').then(res => {
            this.listExam = res.data.data;
        })
            .catch(e => {
                console.log(e);
            });
    }
}
</script>

<style scoped>
@import url('../../../public/css/examselection.css');
</style>
