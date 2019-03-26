/**
 * input輸入框快速選擇image
 */
(function() {
    'use strict';

    /**
     * 全域性生效預設設定
     * 預設設定可以最大程度的減小呼叫時的程式碼
     */
    var defaultSetting = {
        // 可選引數  File Image Camera Image_Camera Image_File Camera_File Text All
        type: 'ALL',
        isMulti: false,
        container: ''
    };

    function extend(target) {
        var finalTarget = target;

        for (var _len = arguments.length, rest = Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
            rest[_key - 1] = arguments[_key];
        }

        rest.forEach(function(source) {
            source && Object.keys(source).forEach(function(key) {
                finalTarget[key] = source[key];
            });
        });

        return finalTarget;
    }

    function selector(element) {
        var target = element;

        if (typeof target === 'string') {
            target = document.querySelector(target);
        }

        return target;
    }

    /**
     * 從一個file物件,載入對應的資料
     * FileReader的方法
     * 方法名              引數              描述
     * readAsBinaryString   file            將檔案讀取為二進位制編碼
     * readAsText           file,[encoding] 將檔案讀取為文字
     * readAsDataURL        file            將檔案讀取為DataURL
     * abort                (none)          終端讀取操作
     * @param {FileReader} oFReader 對應的載入器
     * @param {File} file 檔案物件,選擇的是img型別
     * @param {Function} success 成功載入完畢後的回撥,回撥result(不同的載入方式result型別不同)
     * @param {Function} error 失敗回撥
     * @return {FileReader} 返回檔案載入器物件
     * @param {String} type 型別,DataUrl還是Text還是Binary
     */
    function loadDataFromFile(oFReader, file, success, error, type) {
        if (window.FileReader || !oFReader || !(oFReader instanceof FileReader)) {
            oFReader.onload = function(oFREvent) {
                // 解決DataUrl模式下的b64字串不正確問題
                var b64 = oFREvent.target.result;

                if (type === 'DataUrl') {
                    // 正常的圖片應該是data:image/gif;data:image/png;;data:image/jpeg;data:image/x-icon;
                    // 而在Android的一些5.0系統以下(如4.0)的裝置中,有些返回的b64字串缺少關鍵image/gif標識,所以需要手動加上
                    if (b64 && b64.indexOf('data:base64,') !== -1) {
                        // 去除舊有的錯誤頭部
                        b64 = b64.replace('data:base64,', '');
                        var dataType = '';
                        // 檔名字
                        var name = file.name;

                        if (name && name.toLowerCase().indexOf('.jpg') !== -1) {
                            // jpeg
                            dataType = 'image/jpeg';
                        } else if (name && name.toLowerCase().indexOf('.png') !== -1) {
                            // png
                            dataType = 'image/png';
                        } else if (name && name.toLowerCase().indexOf('.gif') !== -1) {
                            // gif
                            dataType = 'image/gif';
                        } else if (name && name.toLowerCase().indexOf('.icon') !== -1) {
                            // x-icon
                            dataType = 'image/x-icon';
                        }
                        b64 = 'data:' + dataType + ';base64,' + b64;
                    }
                }
                success && success(b64);
            };
            oFReader.onerror = function(error) {
                error && error(error);
            };
            if (type === 'DataUrl') {
                oFReader.readAsDataURL(file);
            } else if (type === 'Text') {
                oFReader.readAsText(file);
            } else {
                oFReader.readAsBinaryString(file);
            }

            return oFReader;
        } else {
            error && error('錯誤:FileReader不存在!');
        }
    }

    /**
     * 構造一個 FileInpput 物件
     * @param {Object} options 配置引數
     * @constructor
     */
    function FileInput(options) {

        options = extend({}, defaultSetting, options);

        this.container = selector(options.container);
        this.options = options;

        this._init();
        this._addEvent();

    }

    FileInput.prototype = {

        /**
         * 初始化 type isMulti filter等
         */
        _init: function() {
            var options = this.options,
                container = this.container,
                isEjs = /EpointEJS/.test(navigator.userAgent);;

            // 設定單個檔案選擇需要的 屬性
            container.setAttribute('type', 'file');

            if (options.isMulti) {
                container.setAttribute('multiple', 'multiple');
            } else {
                container.removeAttribute('multiple');
            }

            var accept = options.accept || container.getAttribute('accept');
            var type = options.type || 'File';
            var filter;

            if (type === 'Image') {
                filter = 'image/*';
                type = 'DataUrl';
            } else if (type === 'Camera') {
                if (isEjs) {
                    filter = 'camera/*';
                } else {
                    filter = 'image/*';
                }
                type = 'DataUrl';
            } else if (type === 'Image_Camera') {
                if (isEjs) {
                    filter = 'image_camera/*';
                } else {
                    filter = 'image/*';
                }
                type = 'DataUrl';
            } else if (type === 'Image_File') {
                if (isEjs) {
                    filter = 'image_file/*';
                } else {
                    filter = '*';
                }
                type = 'DataUrl';
            } else if (type === 'Camera_File') {
                if (isEjs) {
                    filter = 'camera_file/*';
                } else {
                    filter = '*';
                }
                type = 'DataUrl';
            } else if (type === 'Text') {
                filter = 'file/*';
                type = 'Text';

            } else if (type === 'File') {
               if (isEjs) {
                    filter = 'file/*';
                    type = 'File';
                } else {
                    filter = '*';
                    type = 'File';
                }
            } else if (type === 'All') {
                if (isEjs) {
                    filter = '*/*';
                    type = 'DataUrl';
                } else {
                    filter = '*';
                    type = 'DataUrl';
                }
            } else {
                filter = '*';
                type = 'File';
            }
            this.dataType = type;
            filter = accept || filter;
            container.setAttribute('accept', filter);
        },

        /**
         * 增加事件，包括
         * 輪播圖片的監聽
         * 圖片滑動的監聽，等等
         */
        _addEvent: function() {
            var container = this.container,
                options = this.options,
                success = options.success,
                error = options.error,
                self = this;

            // 選擇的回撥中進行預處理
            var changeHandle = function() {
                var aFiles = container.files;
                var len = aFiles.length;

                if (len === 0) {
                    return;
                }
                // 定義檔案讀取器和字尾型別過濾器
                var oFReader = new window.FileReader();
                var index = 0;

                var chainCall = function() {
                    if (index >= len) {
                        return;
                    }
                    loadDataFromFile(oFReader, aFiles[index], function(b64Src) {
                        success && success(b64Src, aFiles[index], {
                            index: index,
                            len: len,
                            isEnd: (index >= len - 1)
                        });
                        index++;
                        chainCall();
                    }, error, self.dataType);
                };

                chainCall();
            };

            container.addEventListener('change', changeHandle);

            // 註冊一個委託物件，方便取消
            this.delegatesHandle = changeHandle;
        },

        /**
         * 將需要暴露的destroy繫結到了 原型鏈上
         */
        destroy: function() {

            this.container.removeEventListener('change', this.delegatesHandle);

            this.container = null;
            this.options = null;
        }
    };

    window.FileInput = FileInput;
})();