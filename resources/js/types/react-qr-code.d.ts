declare module 'react-qr-code' {
    import * as React from 'react';

    export interface QRCodeProps extends React.SVGProps<SVGSVGElement> {
        value: string;
        size?: number;
        bgColor?: string;
        fgColor?: string;
        level?: 'L' | 'M' | 'H' | 'Q';
        title?: string;
    }

    export class QRCode extends React.Component<QRCodeProps, any> {
        render(): React.ReactNode;
    }

    export default QRCode;
}
