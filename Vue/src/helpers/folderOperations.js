import { useStorage } from "../stores/storage";
import axios from "axios";

const storage = useStorage();



export async function createFolder(dir, folderName, fileList) {
    // console.log(dir, folderName);
    try {
        const data = {
            dir: dir,
            folderName: folderName,
        }
        const r = await axios.post(
            `http://localhost:8000/api/folders/createFolder`, data,
            {
                withCredentials: true,
            }
        );

        fileList.unshift({
            name: folderName,
            type: "folder",
            date: r.data,
        });

        return [['新增成功!'], "text-green-500", fileList];
    } catch (e) {
        // console.error(e);
        return [["新增失敗, 請稍後再試!"], "text-red-500", fileList];
    }
}

export async function renameFolder(dir, originName, folderName, fileList) {
    // console.log(dir, originName, folderName);
    try {
        const data = {
            dir: dir,
            originName: originName,
            folderName: folderName
        }
        const r = await axios.put(
            `http://localhost:8000/api/folders/renameFolder`, data,
            {
                withCredentials: true,
            }
        );

        const folder = fileList.find(
            (item) =>
                item.type === "folder" && item.name === originName
        );

        if (folder) {
            folder.name = folderName;
        }

        return [['改名成功!'], "text-green-500", fileList];
    } catch (e) {
        console.error(e);
        return [["改名失敗, 請稍後再試!"], "text-red-500"];
    }
}

export async function deleteFolder(dir, folderName, fileList) {
    // console.log(dir, folderName);
    try {
        const check = confirm("確定要刪除嗎?");
        if (!check) {
            return [["操作已取消!"], "text-red-500"];
        }

        const doubleCheck = confirm("此操作將會一併刪除所有子項目，確定嗎?");
        if (!doubleCheck) {
            return [["操作已取消!"], "text-red-500"];
        }

        const r = await axios.delete(
            `http://localhost:8000/api/folders/deleteFolder?dir=${dir}&folderName=${folderName}`,
            {
                withCredentials: true,
            }
        );

        storage.addUsedStorage(-Number(r.data['size']));

        const folderIndex = fileList.findIndex(
            (item) => item.type === "folder" && item.name === folderName
        );

        if (folderIndex !== -1) {
            fileList.splice(folderIndex, 1);
        }

        return [['刪除成功!'], "text-red-500", true, fileList];
    } catch (e) {
        console.error(e);
        return [["刪除失敗, 請稍後再試!"], "text-red-500", true, fileList];
    }
}