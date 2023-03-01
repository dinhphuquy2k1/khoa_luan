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

        <div class="assessment" v-if="examSessionSelected">
            <div v-for="assessment in listAssessment">
                <div><input type="checkbox" :value="assessment['id']" v-model="selectedAssessment"></div>
                <div class="itemExam" @click="setupAssessment(assessment)">Mã đề {{ assessment['excel_bank_name'] }}</div>
            </div>

            <button @click="btnSave">Lưu</button>
        </div>

        <div class="" v-if="type === 3">
            <input type="file">
        </div>

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
            selectedAssessment: [], //mảng lưu các danh sách đề được chọn

            param: [],
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

        /**
         * Lấy danh sách đề thi
         * **/
        async getAssessment(assessment) {
            this.examSessionSelected = assessment;
            await axios.get('/getAssessmentInfos').then(res => {
                console.log(res);
                this.listAssessment = res.data.data;

            }).catch(e => {
                console.log(e);
            })
        },

        btnSave() {
            if (this.selectedAssessment.length > 0) {
                this.selectedAssessment.forEach(item => {
                    this.param.push({ id_ca_thi: this.examSessionSelected['id'], id_de_thi: item });
                });


                this.axios.post("/create_detail_exam_session", this.param).then(res => {
                    console.log(res);
                    alert("Tạo thành công");
                })
                    .catch(error => {
                        console.log(error);
                    })
            }
            else alert("Vui lòng chọn 1 đề");
        },

        /**
         * chọn 1 đề để thiết lập
         * **/
        setupAssessment(assessment) {
            this.type = 3;
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
