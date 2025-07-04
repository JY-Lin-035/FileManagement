import { defineStore } from 'pinia'
import axios from 'axios';

export const useStorage = defineStore('storage', {
    state: () => ({
        signalStorage: 0,
        usedStorage: 0,
        availableStorage: 0,
        totalStorage: 0,
        percentage: 0,
    }),

    actions: {
        async getFromAPI() {
            try {
                const r = await axios.get(`http://localhost:8000/api/files/getStorage`, { withCredentials: true });
                this.signalStorage = r.data['signalStorage'];
                this.totalStorage = r.data['totalStorage'];
                this.usedStorage = r.data['usedStorage'];
                this.availableStorage = this.totalStorage - this.usedStorage;
                this.percentage = (this.usedStorage / this.totalStorage * 100).toFixed(2);

                this.saveToLocalStorage();
            } catch (e) {
                // console.error(e);
            };
        },

        format(bytes, Type = null) {
            const units = ['B', 'KB', 'MB', 'GB', 'TB'];
            let i = 0;

            if (Type) {
                while (units[i] != Type && i < units.length - 1) {
                    bytes /= 1024;
                    i++;
                }
                return [Math.round(bytes * 100) / 100, null];
            }
            else {
                while (bytes >= 1024 && i < units.length - 1) {
                    bytes /= 1024;
                    i++;
                }
                return [Math.round(bytes * 100) / 100, units[i]];
            }
        },

        addUsedStorage(fileSize) {
            // console.log(this.usedStorage);
            this.usedStorage += fileSize;
            // console.log(this.usedStorage);
            this.availableStorage -= fileSize;
            this.percentage = (this.usedStorage / this.totalStorage * 100).toFixed(2);
            this.saveToLocalStorage();
        },

        saveToLocalStorage() {
            const data = {
                availableStorage: this.availableStorage,
                signalStorage: this.signalStorage,
                usedStorage: this.usedStorage,
                totalStorage: this.totalStorage,
                percentage: this.percentage,
            };
            localStorage.setItem('storage', JSON.stringify(data));
        },

        loadFromLocalStorage() {
            const saved = localStorage.getItem('storage');
            if (saved) {
                const data = JSON.parse(saved)
                this.signalStorage = data.signalStorage || 0;
                this.usedStorage = data.usedStorage || 0;
                this.availableStorage = data.availableStorage || 0;
                this.totalStorage = data.totalStorage || 0;
                // this.percentage = data.percentage || 0;
                this.percentage = (data.usedStorage / data.totalStorage * 100).toFixed(2) || 0;
                return true;
            }
            return false;
        }
    }
})