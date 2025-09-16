<template>
    <div class="media-library">
        <div class="media-library-header">
            <div class="media-library-header-left">媒体库</div>
        </div>
        <div class="media-library-content">
            <div class="content-item">
                <div class="content-item-title">媒体库空间使用情况</div>
                <div class="content-item-box">
                    <div class="box-left">
                        <img class="box-left-img" src="/img/image/image.png" alt="" />
                        <div class="box-left-content">
                            <div class="box-left-content-title">照片</div>
                            <!-- <div class="box-left-content-num">683文件</div> -->
                        </div>
                    </div>
                    <!-- <div class="box-right">6.19G</div> -->
                </div>
                <div class="content-item-box">
                    <div class="box-left">
                        <img class="box-left-img" src="/img/image/video.png" alt="" />
                        <div class="box-left-content">
                            <div class="box-left-content-title">视频</div>
                            <!-- <div class="box-left-content-num">683文件</div> -->
                        </div>
                    </div>
                    <!-- <div class="box-right">6.19G</div> -->
                </div>
            </div>
            <!-- <div class="content-item">
                <div class="content-item-box">
                    <div class="box-left">
                        <img class="box-left-img" src="../assets/image/disk.png" alt="" />
                        <div class="box-left-content">
                            <div class="box-left-content-title">剩余可用空间</div>
                        </div>
                    </div>
                    <div class="box-right">594.16G</div>
                </div>
            </div> -->
        </div>
    </div>
</template>

<script setup lang="ts"></script>

<style scoped lang="scss">
.media-library {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    gap: 16px;

    &-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 14px;
        font-weight: bold;
        border-bottom: 1px solid #e5e5e5;
        padding-bottom: 16px;
    }

    .media-library-content {
        display: flex;
        flex-direction: column;
        gap: 16px;

        .content-item {
            display: flex;
            flex-direction: column;
            gap: 16px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e5e5e5;

            &-title {
                font-size: 14px;
            }

            &-box {
                display: flex;
                justify-content: space-between;
                align-items: center;

                .box-left {
                    display: flex;
                    align-items: center;
                    gap: 10px;

                    &-img {
                        width: 48px;
                        height: 48px;
                        border-radius: 10px;
                    }

                    &-content {
                        display: flex;
                        flex-direction: column;
                        gap: 4px;

                        &-title {
                            font-size: 14px;
                            font-weight: bold;
                        }

                        &-num {
                            font-size: 12px;
                            color: #999;
                        }
                    }
                }

                .box-right {
                    font-size: 14px;
                    font-weight: bold;
                }
            }
        }
    }
}
</style>
