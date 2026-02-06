<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import { Badge } from '@/components/ui/badge';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Power, PowerOff, Clock, Shield, Mail, AlertTriangle, Copy, Check, Hammer } from 'lucide-vue-next';
import ConfirmModal from '@/components/Admin/ConfirmModal.vue';
import * as maintenanceRoutes from '@/routes/admin/system/maintenance';

const showToast = (message: string, type: 'success' | 'error' = 'success') => {
    window.dispatchEvent(new CustomEvent('show-toast', { 
        detail: { message, type } 
    }));
};

interface MaintenanceSettings {
    enabled: boolean;
    title: string;
    message: string;
    retry_after: number;
    secret: string;
    allowed_ips: string[];
    bypass_admin: boolean;
    contact_email: string;
    show_eta: boolean;
    start_time: string;
    end_time: string;
}

interface MaintenanceLog {
    id: number;
    action: string;
    reason: string;
    scheduled_start?: string;
    scheduled_end?: string;
    actual_start?: string;
    actual_end?: string;
    admin_name: string;
    created_at: string;
}

interface Props {
    settings: MaintenanceSettings;
    logs: MaintenanceLog[];
}

const props = defineProps<Props>();

const form = ref({
    title: props.settings.title,
    message: props.settings.message,
    retry_after: props.settings.retry_after,
    contact_email: props.settings.contact_email,
    allowed_ips: props.settings.allowed_ips.join('\n'),
});

const scheduleForm = ref({
    start_time: '',
    end_time: '',
    message: '',
});

const isSubmitting = ref(false);
const copiedSecret = ref(false);
const showEnableDialog = ref(false);
const showDisableDialog = ref(false);

const bypassUrl = computed(() => {
    return props.settings.secret ? `${window.location.origin}/${props.settings.secret}` : '';
});

const enableMaintenance = () => {
    showEnableDialog.value = true;
};

const confirmEnableMaintenance = () => {
    showEnableDialog.value = false;
    isSubmitting.value = true;
    router.post(maintenanceRoutes.enable.url(), {
        title: form.value.title,
        message: form.value.message,
        retry_after: form.value.retry_after,
        contact_email: form.value.contact_email,
        allowed_ips: form.value.allowed_ips.split('\n').filter(ip => ip.trim()),
    }, {
        onSuccess: (page: any) => {
            showToast('Maintenance mode enabled successfully', 'success');
            isSubmitting.value = false;
        },
        onError: (errors) => {
            showToast('Failed to enable maintenance mode', 'error');
            isSubmitting.value = false;
        },
    });
};

const disableMaintenance = () => {
    showDisableDialog.value = true;
};

const confirmDisableMaintenance = () => {
    showDisableDialog.value = false;
    isSubmitting.value = true;
    router.post(maintenanceRoutes.disable.url(), {}, {
        onSuccess: () => {
            showToast('Maintenance mode disabled successfully', 'success');
            isSubmitting.value = false;
        },
        onError: () => {
            showToast('Failed to disable maintenance mode', 'error');
            isSubmitting.value = false;
        },
    });
};

const copyBypassUrl = async () => {
    try {
        await navigator.clipboard.writeText(bypassUrl.value);
        copiedSecret.value = true;
        showToast('Bypass URL copied to clipboard', 'success');
        setTimeout(() => {
            copiedSecret.value = false;
        }, 2000);
    } catch (err) {
        showToast('Failed to copy URL', 'error');
    }
};

const scheduleMaintenance = () => {
    if (!scheduleForm.value.start_time || !scheduleForm.value.end_time) {
        showToast('Please select start and end times', 'error');
        return;
    }

    isSubmitting.value = true;
    router.post(maintenanceRoutes.schedule.url(), scheduleForm.value, {
        onSuccess: () => {
            showToast('Maintenance scheduled successfully', 'success');
            scheduleForm.value = { start_time: '', end_time: '', message: '' };
            isSubmitting.value = false;
        },
        onError: () => {
            showToast('Failed to schedule maintenance', 'error');
            isSubmitting.value = false;
        },
    });
};

const getActionBadge = (action: string) => {
    const variants: Record<string, string> = {
        enabled: 'destructive',
        disabled: 'default',
        scheduled: 'secondary',
    };
    return variants[action] || 'default';
};

const formatDateTime = (datetime: string | undefined) => {
    if (!datetime) return 'N/A';
    return new Date(datetime).toLocaleString();
};
</script>

<template>
    <Head title="Maintenance Mode" />
    
    <AdminLayout title="System Maintenance">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        Maintenance Mode
                    </h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Manage site maintenance and scheduled downtime
                    </p>
                </div>
            
                <div class="flex items-center gap-2">
                    <Button 
                        v-if="settings.enabled"
                        variant="destructive"
                        @click="disableMaintenance"
                        :disabled="isSubmitting"
                    >
                        <PowerOff class="mr-2 h-4 w-4" />
                        Disable Maintenance
                    </Button>
                    <Button 
                        v-else
                        class="bg-blue-600 hover:bg-blue-700 text-white shadow-sm border-transparent rounded-xl"
                        @click="enableMaintenance"
                        :disabled="isSubmitting"
                    >
                        <Power class="mr-2 h-4 w-4" />
                        Enable Maintenance
                    </Button>
                </div>
            </div>

            <!-- Content -->
            <div class="overflow-auto rounded-xl">
                <div class="space-y-6">
                    <!-- Current Status Alert -->
                    <Alert v-if="settings.enabled" variant="destructive">
                        <AlertTriangle class="h-4 w-4" />
                        <AlertDescription>
                            <strong>Maintenance Mode is Active</strong> - Your site is currently in maintenance mode.
                            Visitors will see the maintenance page.
                        </AlertDescription>
                    </Alert>

                    <!-- Bypass URL (when enabled) -->
                    <Card v-if="settings.enabled && settings.secret" class="bg-white dark:bg-gray-900/70 border-gray-200 dark:border-gray-800">
                        <CardHeader>
                            <CardTitle class="flex items-center">
                                <Shield class="mr-2 h-5 w-5" />
                                Bypass URL
                            </CardTitle>
                            <CardDescription>
                                Use this URL to access the site during maintenance
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="flex items-center gap-2">
                                <Input 
                                    :value="bypassUrl" 
                                    readonly 
                                    class="font-mono text-sm"
                                />
                                <Button 
                                    variant="outline" 
                                    size="icon"
                                    @click="copyBypassUrl"
                                >
                                    <Check v-if="copiedSecret" class="h-4 w-4 text-green-500" />
                                    <Copy v-else class="h-4 w-4" />
                                </Button>
                            </div>
                        </CardContent>
                    </Card>

                    <div class="grid gap-6 lg:grid-cols-2">
                        <!-- Maintenance Settings -->
                        <Card class="bg-white dark:bg-gray-900/70 border-gray-200 dark:border-gray-800">
                            <CardHeader>
                                <CardTitle>Maintenance Settings</CardTitle>
                                <CardDescription>
                                    Configure maintenance page content and options
                                </CardDescription>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="space-y-2">
                                    <Label for="title">Title</Label>
                                    <Input 
                                        id="title"
                                        v-model="form.title"
                                        placeholder="We'll be back soon!"
                                    />
                                </div>

                                <div class="space-y-2">
                                    <Label for="message">Message</Label>
                                    <Textarea 
                                        id="message"
                                        v-model="form.message"
                                        placeholder="We are performing scheduled maintenance..."
                                        rows="4"
                                    />
                                </div>

                                <div class="space-y-2">
                                    <Label for="contact">Contact Email</Label>
                                    <div class="flex items-center gap-2">
                                        <Mail class="h-4 w-4 text-muted-foreground" />
                                        <Input 
                                            id="contact"
                                            v-model="form.contact_email"
                                            type="email"
                                            placeholder="support@example.com"
                                        />
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <Label for="retry">Retry After (seconds)</Label>
                                    <Input 
                                        id="retry"
                                        v-model.number="form.retry_after"
                                        type="number"
                                        min="60"
                                    />
                                    <p class="text-sm text-muted-foreground">
                                        Tells browsers when to retry ({{ Math.floor(form.retry_after / 60) }} minutes)
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="ips">Allowed IP Addresses</Label>
                                    <Textarea 
                                        id="ips"
                                        v-model="form.allowed_ips"
                                        placeholder="Enter one IP per line&#10;192.168.1.1&#10;10.0.0.1"
                                        rows="3"
                                    />
                                    <p class="text-sm text-muted-foreground">
                                        These IPs can access the site during maintenance
                                    </p>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Right Column: Schedule & Stats -->
                        <div class="space-y-6">
                            <!-- Schedule Maintenance -->
                            <Card class="bg-white dark:bg-gray-900/70 border-gray-200 dark:border-gray-800">
                                <CardHeader>
                                    <CardTitle class="flex items-center">
                                        <Clock class="mr-2 h-5 w-5" />
                                        Schedule Maintenance
                                    </CardTitle>
                                    <CardDescription>
                                        Plan future maintenance windows
                                    </CardDescription>
                                </CardHeader>
                                <CardContent class="space-y-4">
                                    <div class="space-y-2">
                                        <Label for="start">Start Time</Label>
                                        <Input 
                                            id="start"
                                            v-model="scheduleForm.start_time"
                                            type="datetime-local"
                                        />
                                    </div>

                                    <div class="space-y-2">
                                        <Label for="end">End Time</Label>
                                        <Input 
                                            id="end"
                                            v-model="scheduleForm.end_time"
                                            type="datetime-local"
                                        />
                                    </div>

                                    <div class="space-y-2">
                                        <Label for="schedule-message">Reason</Label>
                                        <Textarea 
                                            id="schedule-message"
                                            v-model="scheduleForm.message"
                                            placeholder="Scheduled server upgrade..."
                                            rows="2"
                                        />
                                    </div>

                                    <Button 
                                        class="w-full bg-blue-600 hover:bg-blue-700 text-white"
                                        @click="scheduleMaintenance"
                                        :disabled="isSubmitting"
                                    >
                                        <Clock class="mr-2 h-4 w-4" />
                                        Schedule Maintenance
                                    </Button>
                                </CardContent>
                            </Card>

                            <!-- Quick Stats -->
                            <Card class="bg-white dark:bg-gray-900/70 border-gray-200 dark:border-gray-800">
                                <CardHeader>
                                    <CardTitle>Statistics</CardTitle>
                                </CardHeader>
                                <CardContent class="space-y-2">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-muted-foreground">Status</span>
                                        <Badge :variant="settings.enabled ? 'destructive' : 'default'">
                                            {{ settings.enabled ? 'Active' : 'Inactive' }}
                                        </Badge>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-muted-foreground">Total Logs</span>
                                        <span class="font-semibold text-gray-900 dark:text-gray-100">{{ logs.length }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-muted-foreground">Allowed IPs</span>
                                        <span class="font-semibold text-gray-900 dark:text-gray-100">{{ settings.allowed_ips.length }}</span>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </div>

                    <!-- Maintenance History -->
                    <Card class="bg-white dark:bg-gray-900/70 border-gray-200 dark:border-gray-800">
                        <CardHeader>
                            <CardTitle>Maintenance History</CardTitle>
                            <CardDescription>
                                Recent maintenance mode activities
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div 
                                    v-for="log in logs" 
                                    :key="log.id"
                                    class="flex items-start gap-4 border-b border-gray-200 dark:border-gray-800 pb-4 last:border-0"
                                >
                                    <Badge :variant="getActionBadge(log.action)">
                                        {{ log.action }}
                                    </Badge>
                                    <div class="flex-1 space-y-1">
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ log.reason }}</p>
                                        <div class="flex gap-4 text-xs text-muted-foreground">
                                            <span>By: {{ log.admin_name }}</span>
                                            <span v-if="log.actual_start">
                                                Started: {{ formatDateTime(log.actual_start) }}
                                            </span>
                                            <span v-if="log.actual_end">
                                                Ended: {{ formatDateTime(log.actual_end) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="logs.length === 0" class="text-center py-8 text-muted-foreground">
                                    No maintenance history yet
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- Enable Maintenance Confirmation Modal -->
            <ConfirmModal
                v-model:show="showEnableDialog"
                title="Enable Maintenance Mode"
                message="Are you sure you want to enable maintenance mode? This will make the site inaccessible to visitors."
                confirm-text="Enable Maintenance"
                cancel-text="Cancel"
                variant="warning"
                @confirm="confirmEnableMaintenance"
            />

            <!-- Disable Maintenance Confirmation Modal -->
            <ConfirmModal
                v-model:show="showDisableDialog"
                title="Disable Maintenance Mode"
                message="Are you sure you want to disable maintenance mode? The site will become accessible to all visitors again."
                confirm-text="Disable Maintenance"
                cancel-text="Cancel"
                variant="danger"
                @confirm="confirmDisableMaintenance"
            />
        </div>
    </AdminLayout>
</template>
