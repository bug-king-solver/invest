<template>
    <div id="app">
        <input type="file" @change="onChange" />
    </div>
</template>

<script>
import XLSX from 'xlsx';

export default {
    name: "UserDataUploader",
    method: {
        onChange(event) {
            this.file = event.target.files ? event.target.files[0] : null;
            let workbook = XLSX.readFile(this.file);
            console.log('workbook1');
            console.log(workbook);
            console.log('SheetNames');
            console.log(workbook.SheetNames);
        },  
    }
}
</script>