<template>
    <div class="container">
        <div class="wrapper-container">
            <div>
                <div>Vùng chọn</div>
                <div>Sheet index: <input type="text" v-model="sheetIndexFormat">Add Start: <input type="text"
                        v-model="add_startFormat">
                    Add End: <input type="text" v-model="add_endFormat">
                </div>
            </div>
            <div>
                <div class="">
                    <b>Font</b>
                    <div class="body">
                        <div>Font
                            <select name="" id="" type-property="0" property-name="Font" class="ms-format">
                                <option value=""></option>
                                <option value="0">Cambria (Headings)</option>
                                <option value="1">Cambria (Body)</option>
                                <option value="2">Arial</option>
                                <option value="3">Time New Roman</option>
                            </select>
                            Point
                            <input type="text" class="ms-point">
                        </div>
                    </div>
                    <div>
                        <div>Font Style
                            <select name="" id="" type-property="1" property-name="Font Style" class="ms-format">
                                <option value=""></option>
                                <option value="0">Regular</option>
                                <option value="1">Italic</option>
                                <option value="2">Bold</option>
                                <option value="3">Bold Italic</option>
                            </select>
                            Point
                            <input type="text" class="ms-point">
                        </div>
                    </div>
                    <div>Font Size <input type="text" type-property="2" property-name="Font Size"
                            class="ms-format">Point<input type="text" class="ms-point"></div>
                    <div>Font Underline
                        <select name="" id="" type-property="3" property-name="Font Underline" class="ms-format">
                            <option value=""></option>
                            <option value="0">Single</option>
                            <option value="1">Double</option>
                            <option value="2">Single Accounting</option>
                            <option value="3">Double Accounting</option>
                        </select>
                        Point
                        <input type="text" class="ms-point">
                    </div>
                    <div>Font Color
                        <select name="" id="" type-property="4" property-name="Font Color" class="ms-format">
                            <option value=""></option>
                            <option value="DarkRed">Dark Red</option>
                            <option value="Red">Red</option>
                            <option value="Orange">Orange</option>
                            <option value="Yellow">Yellow</option>
                            <option value="LightGreen">LightGreen</option>
                            <option value="Green">Green</option>
                            <option value="LightBlue">LightBlue</option>
                            <option value="Blue">Blue</option>
                            <option value="DarkBlue">DarkBlue</option>
                            <option value="Purple">Purple</option>
                        </select>
                        Point
                        <input type="text" class="ms-point">
                    </div>
                    <div>Font Effect
                        <select name="" id="" type-property="5" property-name="Font Effect" class="ms-format">
                            <option value=""></option>
                            <option value="0">Strikethrough</option>
                            <option value="1">Superscript</option>
                            <option value="2">Subscript</option>
                        </select>
                        Point <input type="text" class="ms-point">
                    </div>

                </div>
                <div class="">
                    <b>Alignment</b>
                    <div class="body">
                        Horizontal
                        <select name="" id="" type-property="7" property-name="Text Horizontal" class="ms-format">
                            <option value=""></option>
                            <option value="1">Left (indent)</option>
                            <option value="2">Center</option>
                            <option value="3">Right (indet)</option>
                            <option value="4">Fill</option>
                            <option value="5">Justify</option>
                            <option value="6">Center Across Selection</option>
                            <option value="7">Distrubuted</option>
                        </select>
                        Point
                        <input type="text" class="ms-point">
                    </div>
                    <div class="body">
                        Vertical
                        <select name="" id="" type-property="8" property-name="Text Vertical" class="ms-format">
                            <option value=""></option>
                            <option value="0">Top</option>
                            <option value="1">Center</option>
                            <option value="2">Bottom</option>
                            <option value="3">Justify</option>
                        </select>
                        Point
                        <input type="text" class="ms-point">
                    </div>
                    <div class="body">
                        Text control
                        <select name="" id="" type-property="10" property-name="Text Control" class="ms-format">
                            <option value=""></option>
                            <option value="0">Wrap Text</option>
                            <option value="1">Shrink To Fit</option>
                            <option value="2">Merge Cells</option>
                        </select>
                        Point
                        <input type="text" class="ms-point">
                    </div>
                    <div>
                        Text direction
                        <select name="" id="">
                            <option value=""></option>
                            <option value=""></option>
                            <option value=""></option>
                            <option value=""></option>
                            <option value=""></option>
                            <option value=""></option>
                        </select>
                        Point
                        <input type="text">
                    </div>
                    <div>
                        Text Orientation
                        <select name="" id="">
                            <option value=""></option>
                        </select>
                        Point
                        <input type="text">
                    </div>
                </div>
                <div class="">
                    <b>Cell size</b>
                    <div>
                        Column width
                        <input type="text" type-property="26" property-name="Column Width" class="ms-format">
                        Point
                        <input type="text" name="" id="" class="ms-point">
                    </div>
                    <div>
                        Row height
                        <input type="text" type-property="25" property-name="Row Height" class="ms-format">
                        Point
                        <input type="text" name="" id="" class="ms-point">
                    </div>
                </div>
                <div class="">
                    <b>Sheet Name</b>
                    <div>
                        <input type="text" type-property="27" property-name="Sheet Name" class="ms-format">
                        Point
                        <input type="text" name="" id="" class="ms-point">
                    </div>
                </div>
                <div class="">
                    <b>Number</b>
                    <select name="" id="" v-model="type_number" type-property="28" property-name="Format Number"
                        class="ms-format">
                        <option value=""></option>
                        <option value="0">Number</option>
                        <option value="1">Currency</option>
                        <option value="2">Accounting</option>
                        <option value="3">Date</option>
                        <option value="4">Time</option>
                        <option value="5">Text</option>
                    </select>

                    <div v-if="type_number == 0 || type_number == 1 || type_number == 2">
                        Decimal places: <input type="number" v-model="decimalNumber" max="0">
                        <div v-if="type_number == 0">
                            <input type="radio" @change="isUse1000Separator = !isUse1000Separator">
                            Use 1000 Separator(,)
                        </div>

                        <div v-if="type_number == 1 || type_number == 2">
                            Symbol
                            <select name="" id="" v-model="symbolNumber">
                                <option value="0">None</option>
                                <option value="FORMAT_ACCOUNTING_VIETNAMESE">đ
                                    Vietnamese</option>
                            </select>
                        </div>


                        <div v-if="type_number != 2">
                            Negative numbers <select name="" id="" v-model="negativeNumbers">
                                <option value="0">-1234.10</option>
                                <option value="1" style="color:red;">-1234.10</option>
                                <option value="2">(-1234.10)</option>
                                <option value="3" style="color:red;">(-1234.10)</option>
                            </select>
                        </div>

                    </div>

                    <div>
                        Point <input type="text" class="ms-point">
                    </div>

                </div>
                <div class="">
                    <b>Table</b>
                    <div>
                        Table Style
                        <select name="" id="" type-property="14" property-name="Table Style" class="ms-format">
                            <option value=""></option>
                            <option value="13">Table Style Light 14</option>
                            <option value="26">Table Style Medium 6</option>
                            <option value="36">Table Style Medium 16</option>
                            <option value="32">Table Style Medium 12</option>
                        </select>
                        Point
                        <input type="text" class="ms-point">
                    </div>
                    <div>
                        Table Name
                        <input type="text" type-property="13" property-name="Table Style" class="ms-format">
                        Point
                        <input type="text" class="ms-point">
                    </div>
                </div>
                <div class="">
                    <b>Page Layout</b>
                    <div class="body">
                        Page Orientation
                        <select name="" id="" type-property="20" property-name="Page Orientation" class="ms-format">
                            <option value=""></option>
                            <option value="0">Portrait</option>
                            <option value="1">Landscape</option>
                        </select>
                        Point
                        <input type="text" class="ms-point">
                    </div>
                    <div class="body">
                        Page Size

                        <select name="" id="" type-property="21" property-name="Page Size" class="ms-format">
                            <option value=""></option>
                            <option value="6">A4</option>
                            <option value="7">A5</option>
                            <option value="5">A3</option>
                            <option value=""></option>
                        </select>
                        Point
                        <input type="text" class="ms-point">
                    </div>
                    <div class="body">
                        Border Presets
                        <select name="" id="">
                            <option value=""></option>
                            <option value=""></option>
                            <option value=""></option>
                            <option value=""></option>
                            <option value=""></option>
                        </select>
                        Point
                        <input type="text">
                    </div>
                </div>
                <div class="">
                    <b>Fill</b>
                    <div class="body">
                        Background color
                        <select name="" id="">
                            <option value=""></option>
                            <option value=""></option>
                            <option value=""></option>
                            <option value=""></option>
                            <option value=""></option>
                        </select>
                        Point
                        <input type="text">
                    </div>
                    <div class="body">
                        Fill Effects

                        <select name="" id="">
                            <option value=""></option>
                            <option value=""></option>
                            <option value=""></option>
                            <option value=""></option>
                            <option value=""></option>
                        </select>
                        Point
                        <input type="text">
                    </div>
                    <div class="body">
                        Border Presets
                        <select name="" id="">
                            <option value=""></option>
                            <option value=""></option>
                            <option value=""></option>
                            <option value=""></option>
                            <option value=""></option>
                        </select>
                        Point
                        <input type="text">
                    </div>
                </div>
                <button @click="btnSaveFormat" class="btnSaveFormat" style="width: 300px; height: 56px;">Submit</button>
            </div>

            <div>
                <div class="flex-row">
                    <b>Insert Text</b>
                    <div>
                        Sheet index
                        <input type="text" v-model="sheetInsertText">
                    </div>
                    <div>
                        Range:
                        <input type="text" v-model="rangeInsertText">
                    </div>
                    <div>
                        Content
                        <input type="text" v-model="contentInsertText">
                    </div>
                    <div>
                        Point
                        <input type="text" v-model="pointInsertText">
                    </div>
                </div>
                <button @click="btnSaveText">Insert Text</button>
            </div>
            <div>
                <div class="flex-row">
                    <b>Advanced Filter</b>
                    <div>
                        Sheet index
                        <input type="text" v-model="sheetIndexAdvanced">
                    </div>
                    <div>
                        Range:
                        <input type="text" v-model="rangeAdvanced">
                    </div>
                    <div>
                        List Range:
                        <div>
                            Sheet Index:
                            <input type="text" v-model="sheetIndexListRange">
                        </div>
                        <div>
                            add_start:
                            <input type="text" v-model="addStartListRange">
                            add_end:
                            <input type="text" v-model="addEndListRange">
                        </div>
                    </div>
                    <div>
                        Criteria Range:
                        <div>
                            Title
                            <div>
                                Range
                                <input type="text" v-model="addStartCriteriaRange">
                            </div>
                            <div>
                                content
                                <input type="text" v-model="titleCriteria">
                            </div>
                        </div>
                        <div>
                            Criteria
                            <div>
                                Range
                                <input type="text" v-model="addEndCriteriaRange">
                            </div>
                            <div>
                                Content
                                <input type="text" v-model="valueCriteria">
                            </div>
                        </div>
                    </div>
                    <div>
                        Point
                        <input type="text" v-model="pointAdvanced">
                    </div>
                </div>
                <button @click="btnSaveText">Insert Text</button>
            </div>

            <button @click="btnSaveAdvancedFilter">Save Advanced Filter</button>


            <div>
                <div class="flex-row">
                    <b>Formula</b>
                    <div>
                        Sheet index
                        <input type="text" v-model="sheetIndexFormula">
                    </div>
                    <div>
                        Range:
                        <input type="text" v-model="rangeFormula">
                    </div>
                    <div>
                        <select name="" id="" v-model="type_formula">
                            <option value=""></option>
                            <option value="13">COUNT IF</option>
                            <option value="18">SUM IF</option>
                            <option value="15">MAX</option>
                            <option value="16">MIN</option>
                            <option value="17">AVERAGE</option>
                            <option value="20">IF</option>
                            <option value="21">EXPRESSION</option>
                        </select>
                    </div>
                    <div>
                        Content
                        <input type="text" v-model="contentFormula">
                    </div>
                    <div>
                        Point
                        <input type="text" v-model="pointFormula">
                    </div>
                </div>
                <button @click="btnSaveFormula">Insert Text</button>
            </div>
        </div>
        <button @click="saveData">Save Data</button>
    </div>
</template>

<script>

export default {
    data() {
        return {
            type_number: null,
            negativeNumbers: 0,
            symbolNumber: 0,
            decimalNumber: 0,
            isUse1000Separator: false,


            sheetIndexFormat: null,
            add_endFormat: null,
            add_startFormat: null,
            criteria: {
                id_sheet: null,
                add_start: null,
                add_end: null,
                id_de_thi: 1,
                data_reference: null,
                content: null,
                property_name: null,
                type_property: null,
                point: null
            },
            listCriteria: [],


            sheetInsertText: null,
            rangeInsertText: null,
            contentInsertText: null,
            pointInsertText: null,


            //advanced filter
            sheetIndexAdvanced: null,
            rangeAdvanced: null,
            sheetIndexListRange: null,
            addStartListRange: null,
            addEndListRange: null,
            addStartCriteriaRange: null,
            addEndCriteriaRange: null,
            titleCriteria: null,
            valueCriteria: null,
            pointAdvanced: null,


            //formula
            sheetIndexFormula: null,
            rangeFormula: null,
            contentFormula: null,
            type_formula: null,
            pointFormula: null,

        }
    },
    methods: {
        saveData() {
            this.btnSaveFormula();
            this.btnSaveAdvancedFilter();
            this.btnSaveText();
            this.btnSaveFormat();
            console.log(this.listCriteria);
            this.axios.post('/saveData', this.listCriteria).then(res => {
                console.log(res);
            })
                .catch(error => {
                    console.log(error);
                })
        },
        btnSaveFormula() {
            if (!this.sheetIndexFormula) {
                return;
            }
            if (!this.rangeFormula) {
                return;
            }
            if (!this.contentFormula) {
                return;
            }
            if (!this.type_formula) {
                return;
            }
            if (!this.pointFormula) {
                return;
            }

            this.criteria.add_start = this.rangeFormula;
            this.criteria.content = JSON.stringify([
                this.type_formula,
                this.contentFormula,
            ]);

            this.criteria.id_sheet = this.sheetIndexFormula;
            this.criteria.point = this.pointFormula;
            this.criteria.property_name = "Formula";
            this.criteria.type_property = 24;
            this.listCriteria.push({ ...this.criteria });
            console.log(this.listCriteria);


        },
        btnSaveAdvancedFilter() {
            if (!this.sheetIndexAdvanced) {
                return;
            }
            if (!this.rangeAdvanced) {
                return;
            }
            if (!this.sheetIndexListRange) {
                return;
            }
            if (!this.addStartListRange) {
                return;
            }
            if (!this.addEndListRange) {
                return;
            }
            if (!this.addStartCriteriaRange) {
                return;
            }
            if (!this.addEndCriteriaRange) {
                return;
            }
            if (!this.titleCriteria) {
                return;
            }
            if (!this.valueCriteria) {
                return;
            }
            this.criteria.id_sheet = this.sheetIndexAdvanced;
            this.criteria.add_start = this.rangeAdvanced;

            let data_reference = {
                id_sheet: this.sheetIndexListRange,
                add_start: this.addStartListRange,
                add_end: this.addEndListRange,
            }
            this.criteria.data_reference = JSON.stringify(data_reference);

            this.criteria.content = JSON.stringify([{
                add_start: this.addStartCriteriaRange,
                add_end: this.addEndCriteriaRange,
                title: this.titleCriteria,
                value: this.valueCriteria
            }]);
            this.criteria.point = this.pointAdvanced;
            this.criteria.type_property = 22;
            this.criteria.property_name = "Advanced Filter";
            this.listCriteria.push({ ...this.criteria });
            console.log(this.listCriteria);

        },
        btnSaveText() {
            if (this.sheetInsertText && this.rangeInsertText && this.pointInsertText && this.contentInsertText) {
                this.criteria.id_sheet = this.sheetInsertText;
                this.criteria.add_start = this.rangeInsertText;
                this.criteria.point = this.pointInsertText;
                this.criteria.type_property = 23;
                this.criteria.property_name = "Text";
                this.criteria.content = this.contentInsertText;
                this.listCriteria.push({ ...this.criteria });
            }
        },
        btnSaveFormat() {
            if (this.sheetIndexFormat && this.add_startFormat) {
                const typeFormat = document.querySelectorAll('.ms-format');
                const pointFormat = document.querySelectorAll('.ms-point');
                for (let index = 0; index < typeFormat.length; index++) {
                    const type_property = typeFormat[index].getAttribute('type-property');
                    const property_name = typeFormat[index].getAttribute('property-name');
                    let content = typeFormat[index].value ? typeFormat[index].value : "";


                    const point = pointFormat[index].value;
                    if (content != "" && point != "") {
                        if (type_property == 28) {
                            switch (Number(this.type_number)) {
                                case 0:
                                    content = JSON.stringify({
                                        type: 0,
                                        decimal: this.decimalNumber,
                                        isUse: this.isUse1000Separator,
                                        negativeNumbers: this.negativeNumbers,
                                    });

                                    console.log(content);
                                    break;
                                //currency
                                case 1:
                                    content = JSON.stringify({
                                        type: 1,
                                        decimal: this.decimalNumber,
                                        symbol: this.symbolNumber,
                                        negativeNumbers: this.negativeNumbers,
                                    });
                                    break;
                                case 2:
                                    content = JSON.stringify({
                                        type: 2,
                                        decimal: this.decimalNumber,
                                        symbol: this.symbolNumber,
                                    });
                                    alert(2);
                                    break;
                                case 3:
                                    alert(3);
                                    break;
                                case 4:
                                    alert(4);
                                    break;
                                case 5:
                                    alert(5);
                                    break;
                                default:
                                    alert('default');
                                    break;
                            }
                        }
                        this.criteria.id_sheet = this.sheetIndexFormat;
                        this.criteria.add_end = this.add_endFormat;
                        this.criteria.add_start = this.add_startFormat;
                        this.criteria.content = content;
                        this.criteria.property_name = property_name;
                        this.criteria.type_property = type_property;
                        this.criteria.point = point;
                        this.listCriteria.push({ ...this.criteria });

                    }
                }
            }
            else {
                alert(1);
            }

            console.log(this.listCriteria);

        }
    },
    mounted() {
        console.log('Component mounted.')
    }
}
</script>

<style scoped>
@import url(../../../public/css/index.css);

.container {
    width: 100%;
    height: 100vh;
    display: flex;
    justify-content: center;
}

.wrapper-container {
    width: 50%;
}

.btnSave {
    width: 300px;
    height: 56px;
    background-color: antiquewhite;
    border: none;
}

.btnSave:hover {
    cursor: pointer;
}
</style>
