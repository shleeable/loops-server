<template>
    <SettingsLayout>
        <div class="p-6">
            <div class="flex items-center gap-3 mb-6">
                <button
                    class="flex items-center text-gray-500 hover:text-gray-400 cursor-pointer"
                    @click="$router.back()"
                >
                    <i class="bx bx-arrow-back text-[20px] mr-1"></i>
                </button>
                <h1
                    class="text-2xl font-semibold tracking-tight dark:text-gray-300"
                >
                    Security
                </h1>
            </div>
            <hr class="border-gray-300 dark:border-gray-700" />

            <section class="my-8">
                <h2 class="tracking-tight font-light mb-4 dark:text-gray-300">
                    Password and authentication
                </h2>
                <div class="flex flex-col gap-3 mb-6">
                    <div
                        class="bg-white dark:bg-slate-950 rounded-lg shadow-sm"
                    >
                        <div class="flex justify-between items-center p-4">
                            <div class="flex items-center gap-5">
                                <h3 class="font-medium mb-0 dark:text-gray-300">
                                    Password
                                </h3>
                            </div>
                            <div class="flex items-center">
                                <button
                                    class="text-sm text-blue-500 font-medium cursor-pointer hover:text-blue-400 transition-colors"
                                    @click="showChangePassword = true"
                                >
                                    Change
                                </button>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-slate-950 rounded-lg shadow-sm"
                    >
                        <div
                            class="px-4 py-6 flex items-center justify-between"
                        >
                            <div class="flex flex-col max-w-[60%]">
                                <h3 class="font-medium mb-2 dark:text-gray-300">
                                    Two-factor authentication
                                </h3>
                                <p class="text-xs text-gray-500 font-light">
                                    Add an extra layer of security to your
                                    account with 2FA using an authenticator app.
                                </p>
                            </div>
                            <div class="flex items-center gap-8">
                                <span
                                    class="text-sm"
                                    :class="
                                        twoFactorEnabled
                                            ? 'bg-green-100 p-2 rounded-lg text-green-600'
                                            : 'text-gray-500'
                                    "
                                >
                                    {{
                                        twoFactorEnabled
                                            ? "Enabled"
                                            : "Disabled"
                                    }}
                                </span>
                                <button
                                    v-if="twoFactorEnabled"
                                    class="text-sm font-medium cursor-pointer"
                                    :class="
                                        twoFactorEnabled
                                            ? 'text-red-500'
                                            : 'text-blue-500'
                                    "
                                    @click="disableTwoFactor"
                                >
                                    Disable
                                </button>
                                <button
                                    v-else
                                    class="text-sm font-medium cursor-pointer"
                                    :class="
                                        twoFactorEnabled
                                            ? 'text-red-500'
                                            : 'text-blue-500'
                                    "
                                    @click="enableTwoFactor"
                                >
                                    Enable
                                </button>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="isSettingUp2FA"
                        class="bg-white dark:bg-slate-950 rounded-lg shadow-sm"
                    >
                        <div class="p-6">
                            <h3
                                class="text-lg font-semibold mb-4 dark:text-gray-300"
                            >
                                Setup Two-Factor Authentication
                            </h3>

                            <div class="space-y-6">
                                <div class="text-center">
                                    <h4
                                        class="font-medium mb-2 dark:text-gray-300"
                                    >
                                        Step 1: Scan QR Code
                                    </h4>
                                    <p class="text-sm text-gray-500 mb-4">
                                        Scan this QR code with your
                                        authenticator app (Google Authenticator,
                                        Authy, etc.)
                                    </p>

                                    <div class="flex justify-center mb-4">
                                        <div
                                            class="bg-white p-4 rounded-lg border border-gray-200 dark:border-gray-700 inline-block"
                                        >
                                            <div
                                                v-html="qrCode"
                                                class="w-48 h-48"
                                            ></div>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <h4
                                        class="font-medium mb-2 dark:text-gray-300"
                                    >
                                        Step 2: Enter Verification Code
                                    </h4>
                                    <p class="text-sm text-gray-500 mb-4">
                                        Enter the 6-digit code from your
                                        authenticator app
                                    </p>

                                    <div class="max-w-xs mx-auto">
                                        <div class="relative">
                                            <input
                                                type="text"
                                                v-model="twoFactorVerifyCode"
                                                maxlength="6"
                                                placeholder="000000"
                                                class="w-full px-4 py-3 text-center text-lg font-mono tracking-widest border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-900 dark:text-gray-100"
                                                :class="{
                                                    'border-red-500 focus:ring-red-500 focus:border-red-500':
                                                        has2FAError,
                                                    'pr-10': isVerifying,
                                                }"
                                                @input="handle2FAInput"
                                                @keyup.enter="verify2FA"
                                                :disabled="isVerifying"
                                            />
                                            <div
                                                v-if="isVerifying"
                                                class="absolute right-3 top-1/2 transform -translate-y-1/2"
                                            >
                                                <div
                                                    class="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-500"
                                                ></div>
                                            </div>
                                        </div>

                                        <div
                                            v-if="has2FAError"
                                            class="mt-2 text-sm text-red-600 dark:text-red-400"
                                        >
                                            {{ twoFactorError }}
                                        </div>
                                    </div>
                                </div>

                                <div class="flex gap-3 justify-center pt-4">
                                    <button
                                        class="px-6 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors cursor-pointer"
                                        @click="cancel2FASetup"
                                        :disabled="isVerifying"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        class="px-6 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                                        @click="verify2FA"
                                        :disabled="
                                            !twoFactorVerifyCode ||
                                            twoFactorVerifyCode.length !== 6 ||
                                            isVerifying
                                        "
                                    >
                                        <div
                                            v-if="isVerifying"
                                            class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"
                                        ></div>
                                        {{
                                            isVerifying
                                                ? "Verifying..."
                                                : "Verify & Enable"
                                        }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <div
            v-if="showChangePassword"
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
        >
            <div
                class="bg-white dark:bg-slate-950 rounded-lg p-6 w-full max-w-md mx-4"
            >
                <h3 class="text-lg font-semibold">Change Password</h3>

                <div v-if="hasPasswordChangeError" class="my-3">
                    <div class="font-bold text-red-500 text-sm">
                        Oops, the following error(s) occured:
                    </div>
                    <div
                        v-for="error in passwordErrors"
                        class="pl-2 text-red-500 text-xs"
                    >
                        {{ error[0] }}
                    </div>
                </div>
                <div v-else class="mb-4"></div>

                <div class="space-y-4">
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Current password</label
                        >
                        <input
                            type="password"
                            v-model="currentPassword"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >New password</label
                        >
                        <input
                            type="password"
                            v-model="newPassword"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Confirm new password</label
                        >
                        <input
                            type="password"
                            v-model="confirmPassword"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                    </div>
                </div>

                <div class="flex gap-3 justify-end mt-6">
                    <button
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors cursor-pointer"
                        @click="cancelChangePassword"
                    >
                        Cancel
                    </button>
                    <button
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors cursor-pointer"
                        @click="changePassword"
                    >
                        Change Password
                    </button>
                </div>
            </div>
        </div>
    </SettingsLayout>
</template>

<script setup>
import { ref, onMounted } from "vue";
import SettingsLayout from "~/layouts/SettingsLayout.vue";
import ToggleSwitch from "@/components/Form/ToggleSwitch.vue";
import axios from "~/plugins/axios";
import { useAlertModal } from "@/composables/useAlertModal.js";

const twoFactorEnabled = ref(false);
const axiosInstance = axios.getAxiosInstance();
const { alertModal, confirmModal } = useAlertModal();
const showChangePassword = ref(false);
const currentPassword = ref("");
const newPassword = ref("");
const confirmPassword = ref("");
const hasPasswordChangeError = ref(false);
const passwordErrors = ref([]);
const qrCode = ref();
const isSettingUp2FA = ref(false);
const twoFactorVerifyCode = ref("");
const isVerifying = ref(false);
const has2FAError = ref(false);
const twoFactorError = ref("");

const loadSecurityConfig = () => {
    axiosInstance
        .get("/api/v1/account/settings/security-config")
        .then((res) => {
            twoFactorEnabled.value = res.data.data.two_factor_enabled;
        });
};

const disableTwoFactor = async () => {
    const result = await confirmModal(
        "Disable Two-Factor Authentication",
        "Are you sure you want to disable two factor authentication? This will make your account less secure.",
        "Disable 2FA",
        "Cancel",
    );

    if (result) {
        await axiosInstance
            .post("/api/v1/account/settings/disable-2fa")
            .then((res) => {
                twoFactorEnabled.value = false;
            })
            .finally(() => {
                alertModal(
                    "Two-Factor Authentication Disabled",
                    "Two-factor authentication has been successfully disabled for your account.",
                );
            });
    }
};

const enableTwoFactor = async () => {
    const result = await confirmModal(
        "Enable Two-Factor Authentication",
        "Are you sure you want to enable two-factor authentication?",
        "Enable",
        "Cancel",
    );

    if (result) {
        await axiosInstance
            .post("/api/v1/account/settings/setup-2fa")
            .then((res) => {
                qrCode.value = res.data.data.qr;
                isSettingUp2FA.value = true;
                twoFactorVerifyCode.value = "";
                has2FAError.value = false;
                twoFactorError.value = "";
            });
    }
};

const handle2FAInput = (event) => {
    const value = event.target.value.replace(/[^\d]/g, "");
    twoFactorVerifyCode.value = value;

    if (has2FAError.value) {
        has2FAError.value = false;
        twoFactorError.value = "";
    }
};

const verify2FA = async () => {
    if (!twoFactorVerifyCode.value || twoFactorVerifyCode.value.length !== 6) {
        has2FAError.value = true;
        twoFactorError.value = "Please enter a 6-digit code";
        return;
    }

    isVerifying.value = true;
    has2FAError.value = false;
    twoFactorError.value = "";

    try {
        await axiosInstance.post("/api/v1/account/settings/confirm-2fa", {
            code: twoFactorVerifyCode.value,
        });

        twoFactorEnabled.value = true;
        isSettingUp2FA.value = false;

        await alertModal(
            "Two-Factor Authentication Enabled",
            "Two-factor authentication has been successfully enabled for your account.",
        );
    } catch (error) {
        has2FAError.value = true;
        if (error.response?.data?.message) {
            twoFactorError.value = error.response.data.message;
        } else if (error.response?.data?.errors?.code) {
            twoFactorError.value = error.response.data.errors.code[0];
        } else {
            twoFactorError.value =
                "Invalid verification code. Please try again.";
        }
    } finally {
        isVerifying.value = false;
    }
};

const cancel2FASetup = () => {
    isSettingUp2FA.value = false;
    twoFactorVerifyCode.value = "";
    has2FAError.value = false;
    twoFactorError.value = "";
    qrCode.value = null;
};

const changePassword = () => {
    try {
        axiosInstance
            .post("/api/v1/account/settings/update-password", {
                current_password: currentPassword.value,
                password: newPassword.value,
                password_confirmation: confirmPassword.value,
            })
            .then((res) => {
                showChangePassword.value = false;
                alertModal(
                    "Success!",
                    "Your password was successfully updated!",
                );
            })
            .catch((err) => {
                hasPasswordChangeError.value = true;
                passwordErrors.value = err.response.data?.errors;
            });
    } catch (err) {
        hasPasswordChangeError.value = true;
        passwordErrors.value = err.response.data?.errors;
    } finally {
        currentPassword.value = "";
        newPassword.value = "";
        confirmPassword.value = "";
    }
};

const cancelChangePassword = () => {
    showChangePassword.value = false;
    currentPassword.value = "";
    newPassword.value = "";
    confirmPassword.value = "";
};

onMounted(() => {
    loadSecurityConfig();
});
</script>
