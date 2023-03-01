<template>
    <div>
        Thiết lập đề

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên đề thi</th>
                    <th>Mô tả</th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody v-for="item in listExcelBank">
                <tr>
                    <td>{{ item['id'] }}</td>
                    <td> {{ item['excel_bank_name'] }}</td>
                    <td>{{ item['mo_ta'] }}</td>
                    <td><a v-bind:href="'/home/' + item['id']">Thiết lập</a></td>
                    <td>Xóa</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>


<script>
export default {
    data() {
        return {
            listExcelBank: [],
        }
    },
    methods: {
        /**
         * Load dữ liệu
         * **/
        loadData() {
            this.axios.get('/api/get_excel_bank').then(res => {
                this.listExcelBank = res.data.data;
            }).catch(e => {
                console.log(e);
            })
        },
    },
    created() {
        this.loadData();
    }
}
</script>

<style scoped>
table {
    border-collapse: collapse;
}

tr th {
    text-align: center;
    width: 180px;
}

tr td {
    padding: 20px;
}

tbody tr:hover td {
    cursor: pointer;
    background-color: antiquewhite;
}
</style>


