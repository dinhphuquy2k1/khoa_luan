<template>
    <div class="detail-box">
        <div class="detail-container">
            <div class="close" @click="showDetail">X</div>
            <div class="detail-header">
                Thông tin đã thiết lập
            </div>
            <div class="detail-content">
                {{ data }}
                <div class="w-full">
                    <table cellspacing="0" cellpadding="0" class="ms-table">
                        <thead class="ms-thead">
                            <tr class="ms-tr tr-1">
                                <th class="ms-th" style="width:30px">
                                    <div class="ms-th-title flex">
                                        <div class="text-left">STT</div>
                                    </div>
                                </th>
                                <th class="ms-th">
                                    <div class="ms-th-title flex">
                                        <div class="text-left">Vùng chọn</div>
                                    </div>
                                </th>
                                <th class="ms-th">
                                    <div class="ms-th-title flex">
                                        <div class="text-left">Tiêu chí</div>
                                    </div>
                                </th>
                                <th class="ms-th" style="width:30px">
                                    <div class="ms-th-title flex">
                                        <div class="text-left">Điểm</div>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="ms-tbody data">
                            <tr class="ms-tr row-selected action-pointer" v-for="(item, index) in data" :key="index">
                                <td class="ms-td ms-col-td EmulationTitleName">
                                    <div class="text-left">
                                        {{ index + 1 }}
                                    </div>
                                </td>
                                <td class="ms-td ms-col-td EmulationTitleName">
                                    <div class="text-left">
                                        {{ 'sheet'+ item.id_sheet + "!" + item.add_start + item.add_end }}
                                    </div>
                                </td>
                                <td class="ms-td ms-col-td EmulationTitleName">
                                    <div class="text-left"></div>
                                </td>
                                <td class="ms-td ms-col-td EmulationTitleName">
                                    <div class="text-left">
                                        {{ item.point }}
                                    </div>
                                </td>

                            </tr>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    props: {
        data: {
            Type: Array
        }
    },
    methods: {
        showDetail() {
            this.$emit("showDetail");
        }
    }
}
</script>
<style>
@import url(../../../public/css/detail.css);
</style>
