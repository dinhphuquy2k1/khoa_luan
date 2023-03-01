<template>
    <div>
        Form tạo ca thi


        <br>
        <h1>Chọn kì thi</h1>
        <div v-if="!examSelected">
            <div v-for="exam in listExam">
                <div class="itemExam" @click="examSelected = exam">Kì thi {{ exam['ten_ki_thi'] }}</div>
            </div>
        </div>


        <div v-if="examSelected">
            Thời gian thi
            <input type="datetime-local" v-model="examSession.thoi_gian">
            Mô tả
            <input type="text" v-model="examSession.mo_ta">
            <div><button @click="btnSave">Tạo</button></div>
        </div>


    </div>
</template>


<script>
export default {
    data() {
        return {
            listExam: [],
            examSelected: null,
            examSession: {
                ma_ca_thi: 1,
                id_ki_thi: null,
                mo_ta: null,
                thoi_gian: null,
            }
        }
    },
    methods: {
        btnSave() {
            if (!this.examSession.thoi_gian) {
                alert("Điền thông tin");
                return;
            }

            this.examSession.id_ki_thi = this.examSelected['id'];
            this.axios.post('/create_exam_session', this.examSession).then(res => {
                console.log(res);
                alert("Tạo thành công");
            })
                .catch(e => {
                    alert("Có lỗi xảy ra");
                    console.log(e);
                });
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
