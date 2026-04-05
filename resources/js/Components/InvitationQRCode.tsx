import { QRCode } from 'react-qr-code';

interface Props {
    invitationCode: string;
    appUrl: string;
}

export default function InvitationQRCode({ invitationCode, appUrl }: Props) {
    const joinUrl = `${appUrl.replace(/\/$/, '')}/couple/join?code=${encodeURIComponent(invitationCode)}`;

    return (
        <>
            <div className="flex justify-center my-3">
                <QRCode
                    value={joinUrl}
                    size={64}
                    viewBox="0 0 256 256"
                />
            </div>
            <p className="font-mono text-xl tracking-widest text-amber-700 font-bold text-center">
                {invitationCode}
            </p>
        </>
    );
}
