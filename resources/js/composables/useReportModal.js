import { ref, computed, inject } from "vue";
import axios from "~/plugins/axios";
import { useAlertModal } from "@/composables/useAlertModal.js";
const { alertModal, confirmModal } = useAlertModal();

const REPORT_CATEGORIES = [
    { key: "1010", message: "Inappropriate and irrelevant search" },
    { key: "1011", message: "Violence, abuse, and criminal exploitation" },
    { key: "1012", message: "Hate and harassment" },
    { key: "1013", message: "Suicide and self-harm" },
    { key: "1014", message: "Disordered eating and unhealthy body image" },
    { key: "1015", message: "Dangerous activities and challenges" },
    { key: "1016", message: "Nudity and sexual content" },
    { key: "1017", message: "Shocking and graphic content" },
    { key: "1018", message: "Misinformation" },
    { key: "1019", message: "Deceptive behavior and spam" },
    { key: "1020", message: "Regulated goods and activities" },
    { key: "1021", message: "Frauds and scams" },
    { key: "1022", message: "Sharing personal information" },
    { key: "1023", message: "Report illegal content" },
    { key: "1024", message: "Counterfeits and intellectual property" },
    { key: "1025", message: "Undisclosed branded content" },
    { key: "1026", message: "Other" },
];

const CATEGORIES_WITH_TEXT_INPUT = [
    "1018", // Misinformation
    "1021", // Frauds and scams
    "1023", // Report illegal content
    "1026", // Other
];

const axiosInstance = axios.getAxiosInstance();

const isOpen = ref(false);
const currentStep = ref(1);
const isSubmitting = ref(false);

const reportType = ref("");
const reportContentId = ref("");
const reportRoutePath = ref("");
const selectedCategory = ref(null);
const additionalText = ref("");

export function useReportModal() {
    const requiresTextInput = computed(() => {
        return (
            selectedCategory.value &&
            CATEGORIES_WITH_TEXT_INPUT.includes(selectedCategory.value.key)
        );
    });

    const canProceedToNextStep = computed(() => {
        if (currentStep.value === 1) {
            return selectedCategory.value !== null;
        }
        if (currentStep.value === 2) {
            return (
                !requiresTextInput.value ||
                additionalText.value.trim().length > 0
            );
        }
        return false;
    });

    function openReportModal(type, contentId, routePath) {
        reportType.value = type;
        reportContentId.value = contentId;
        reportRoutePath.value = routePath;
        isOpen.value = true;
        resetReportModal();
    }

    function closeReportModal() {
        isOpen.value = false;
        resetReportModal();
    }

    function resetReportModal() {
        currentStep.value = 1;
        selectedCategory.value = null;
        additionalText.value = "";
        isSubmitting.value = false;
    }

    function goToNextReportStep() {
        if (canProceedToNextStep.value) {
            currentStep.value = 2;
        }
    }

    function goToPreviousReportStep() {
        if (currentStep.value > 1) {
            currentStep.value = 1;
        }
    }

    function selectCategory(category) {
        selectedCategory.value = category;
    }

    async function submitReport() {
        if (!canProceedToNextStep.value) return;

        isSubmitting.value = true;

        try {
            const reportData = {
                type: reportType.value,
                id: reportContentId.value,
                key: selectedCategory.value.key,
                path: reportRoutePath.value,
                comment: additionalText.value || null,
            };

            await axiosInstance
                .post("/api/v1/report", reportData)
                .then((res) => {
                    closeReportModal();
                })
                .finally(() => {
                    alertModal(
                        "Report Submitted!",
                        "Your report was successfully sent and will be reviewed by our content moderation team.<br /><br />Thank you for helping keeping our community safe ❤️",
                    );
                });
        } catch (error) {
            closeReportModal();

            let msg = "An unexpected error occured";
            if (error.response?.data?.message) {
                msg = error.response.data.message;
            }
            alertModal("Error", msg);
            console.error("Error submitting report:", error);
        } finally {
            isSubmitting.value = false;
        }
    }

    return {
        isOpen,
        currentStep,
        isSubmitting,
        reportType,
        reportContentId,
        reportRoutePath,
        selectedCategory,
        additionalText,

        requiresTextInput,
        canProceedToNextStep,

        REPORT_CATEGORIES,

        openReportModal,
        closeReportModal,
        resetReportModal,
        goToNextReportStep,
        goToPreviousReportStep,
        selectCategory,
        submitReport,
    };
}
