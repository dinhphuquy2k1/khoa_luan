<template>
    <div>
        <div>
            Tên đề thi
            <input type="text" v-model="param.excel_bank_name">
            Mô tả
            <input type="text" v-model="param.mo_ta">
        </div>
        <button @click="btnSave">Submit</button>

        <div v-if="isSuccess">
            Tạo đề thành công
            <div>Bạn có muốn thiết lập đề ko?</div>
            <button>Hủy</button><button @click="setupTest">Thiết lập</button>
        </div>
    </div>
</template>

<script>
import ExampleComponent from './ExampleComponent.vue';
export default {
    components: {
        ExampleComponent
    },
    data() {
        return {
            param: {
                excel_bank_name: "",
                mo_ta: "",
            },

            isSuccess: true,

            isSetup: false,
        }
    },
    methods: {
        btnSave() {
            if (!this.param.excel_bank_name) {
                alert("Nhập thông tin");
                return;
            }

            this.axios.post("/create_excel_bank", this.param).then(res => {
                this.isSuccess = true;
            }).catch(e => {
                console.log(e);
            })
        },

        /**
         * Click nút thiết lập đề
         * **/
        setupTest() {
            this.isSetup = !this.isSetup;
        }
    }
}
</script>

<style></style>
