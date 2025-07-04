import { useStorage } from "../stores/storage";
import axios from "axios";

const storage = useStorage();


// 下載
export function downloadFile(dir, fileName) {
    try {
        const url = `http://localhost:8000/api/files/download?dir=${encodeURIComponent(dir)}&filename=${encodeURIComponent(fileName)}`;


        const tempLink = document.createElement("a");
        tempLink.href = url;
        tempLink.download = fileName;

        document.body.appendChild(tempLink);
        tempLink.click();
        document.body.removeChild(tempLink);

        return ["", "", false];
    } catch (e) {
        console.error(e);
        return [["檔案不存在!"], "text-red-500", true];
    }
}

// 刪除
export async function deleteFile(dir, fileName, deleteItem, fileList) {
    try {
        const fl = fileList;
        const check = confirm("確定要刪除嗎?");
        if (!check) {
            return [["操作已取消!"], "text-red-500"];
        }

        const r = await axios.delete(
            `http://localhost:8000/api/files/delete?dir=${dir}&filename=${fileName}`,
            {
                withCredentials: true,
            }
        );

        fl.splice(
            fl.findIndex((item) => item == deleteItem),
            1
        );

        storage.addUsedStorage(-Number(r.data['size']));

        return [["刪除成功！"], "text-red-500", true, fl];
    } catch (e) {
        console.error(e);
        return [["檔案或資料夾不存在!"], "text-red-500", true, fileList];
    }
}

// 檔案分享
export async function getShareFileLink(dir, fileName) {
    try {
        const data = {
            dir: dir,
            filename: fileName,
        }
        const r = await axios.post(
            `http://localhost:8000/api/share/getLink`, data,
            {
                withCredentials: true,
            }
        );

        // response className showMode shareLink copyShow
        return [fileName, "text-green-500", false, r.data, true];
    } catch (e) {
        console.error(e);
        return [["連結生成失敗, 請稍後再試!"], "text-red-500", true, "", false];
    }
}

// 移除檔案分享
export async function deleteShareFileLink(dir, fileName) {
    try {
        const data = {
            dir: dir,
            filename: fileName,
        }
        const r = await axios.delete(
            `http://localhost:8000/api/share/deleteLink?dir=${dir}&filename=${fileName}`,
            {
                withCredentials: true,
            }
        );


        return [['移除成功!'], "text-red-500", true];
    } catch (e) {
        // console.error(e);
        return [["連結移除失敗, 請稍後再試!"], "text-red-500", true];
    }
}