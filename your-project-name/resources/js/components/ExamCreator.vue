<template>
    <div class="">
        Form tạo kì thi
        <div class="exam">
            <div>
                Kì thi:
                <input type="text" v-model="exam.ten_ki_thi">
                Thời gian thi:
                <input type="datetime-local" v-model="time">
                Mô tả:
                <input type="text" v-model="note">
            </div>
        </div>
        <button @click="btnSubmit">Hoàn thành</button>
    </div>
</template>
<script>


export default {
    data() {
        return {
            index: 0,

            date: null,
            time: null,
            note: null,
            code: null, //mã

            //kì thi
            exam: {
                ten_ki_thi: "",
                thoi_gian: null,
                mo_ta: ""
            },

            //đề thi
            assessment: {
                id_ca_thi: null,
                ma_de_thi: null,
                mo_ta: null,
            },

            param: [],  //mảng lưu toàn bộ dữ liệu
            //ca thi
            examSession: {
                id_ki_thi: null,
                ma_ca_thi: null,
                thoi_gian: null,
                mo_ta: null
            }
        }
    },
    methods: {
        btnSubmit() {
            if (!this.exam.ten_ki_thi || !this.time) {
                alert('điền thông tin');
                return;
            }
            this.exam.thoi_gian = this.time;
            this.exam.mo_ta = this.note;
            this.param.push(this.exam);
            this.axios.post('/saveExam', this.param).then(res => {
                console.log(res);
            })
                .catch(e => {
                    console.log(e);
                });
        },
    },
    created() {

    }
}
</script>
<style scoped>
@import url('../../../public/css/examcreator.css');
</style>
