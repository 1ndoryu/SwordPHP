import {useState, useRef, useCallback} from 'react';

interface UseFileUploadParams {
    onUploadComplete?: () => void;
}

interface UseFileUploadReturn {
    isDragging: boolean;
    fileInputRef: React.RefObject<HTMLInputElement | null>;
    uploading: boolean;
    handleUpload: (fileList: FileList | null) => Promise<void>;
    handleDragOver: (e: React.DragEvent) => void;
    handleDragLeave: (e: React.DragEvent) => void;
    handleDrop: (e: React.DragEvent) => void;
    triggerFileInput: () => void;
}

export const useFileUpload = ({onUploadComplete}: UseFileUploadParams = {}): UseFileUploadReturn => {
    const [isDragging, setIsDragging] = useState(false);
    const [uploading, setUploading] = useState(false);
    const fileInputRef = useRef<HTMLInputElement>(null);

    const handleUpload = useCallback(
        async (fileList: FileList | null) => {
            if (!fileList || fileList.length === 0) return;

            const formData = new FormData();
            formData.append('file', fileList[0]);

            try {
                setUploading(true);
                const res = await fetch('/admin/media/upload', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                const data = await res.json();

                if (data.success) {
                    if (onUploadComplete) {
                        onUploadComplete();
                    }
                } else {
                    alert(data.message || 'Error uploading file');
                }
            } catch (e) {
                alert('Upload failed');
            } finally {
                setUploading(false);
            }
        },
        [onUploadComplete]
    );

    const handleDragOver = useCallback((e: React.DragEvent) => {
        e.preventDefault();
        setIsDragging(true);
    }, []);

    const handleDragLeave = useCallback((e: React.DragEvent) => {
        e.preventDefault();
        setIsDragging(false);
    }, []);

    const handleDrop = useCallback(
        (e: React.DragEvent) => {
            e.preventDefault();
            setIsDragging(false);
            handleUpload(e.dataTransfer.files);
        },
        [handleUpload]
    );

    const triggerFileInput = useCallback(() => {
        fileInputRef.current?.click();
    }, []);

    return {
        isDragging,
        fileInputRef,
        uploading,
        handleUpload,
        handleDragOver,
        handleDragLeave,
        handleDrop,
        triggerFileInput
    };
};
