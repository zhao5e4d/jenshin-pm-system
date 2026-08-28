<?php
declare(strict_types=1);
class jxssoModel extends model
{
    /**
     * 校验 token 并返回匹配用户或失败原因。
     *
     * @param  string $token
     * @access public
     * @return array
     */
    public function loginByToken(string $token): array
    {
        $sso = isset($this->config->jenshin->sso) ? $this->config->jenshin->sso : null;
        if(empty($sso) || empty($sso->enabled)) return array('message' => $this->lang->jxsso->disabled);
        if(empty($sso->secret)) return array('message' => $this->lang->jxsso->notConfigured);

        $payload = $this->verifyJwt($token, (string)$sso->secret);
        if($payload === false) return array('message' => $this->lang->jxsso->invalidToken);

        $now = time();
        $skew = isset($sso->clockSkewSeconds) ? (int)$sso->clockSkewSeconds : 30;
        if(!empty($payload['nbf']) && $now + $skew < (int)$payload['nbf']) return array('message' => $this->lang->jxsso->invalidToken);
        if(empty($payload['exp']) || $now - $skew >= (int)$payload['exp']) return array('message' => $this->lang->jxsso->expiredToken);

        $issuer = isset($sso->issuer) ? (string)$sso->issuer : 'boke-info-pro';
        $audience = isset($sso->audience) ? (string)$sso->audience : 'jenshin-pm-system';
        if(empty($payload['iss']) || (string)$payload['iss'] !== $issuer) return array('message' => $this->lang->jxsso->invalidToken);
        $aud = $payload['aud'] ?? '';
        if(is_array($aud)) $aud = reset($aud);
        if((string)$aud !== $audience) return array('message' => $this->lang->jxsso->invalidToken);

        $phone = isset($payload['phone']) ? (string)$payload['phone'] : '';
        $normalized = $this->normalizeMobile($phone);
        if($normalized === '') return array('message' => $this->lang->jxsso->mobileMissing);

        $users = $this->findUsersByMobile($normalized);
        if(count($users) === 0) return array('message' => $this->lang->jxsso->userNotFound);
        if(count($users) > 1) return array('message' => $this->lang->jxsso->mobileAmbiguous);

        $user = reset($users);
        if($this->loadModel('user')->checkLocked($user->account)) return array('message' => $this->lang->jxsso->userLocked);

        return array('user' => $user);
    }

    /**
     * 规范化手机号：去掉非数字，剥离 86 / +86 前缀后取后 11 位。
     *
     * @param  string $raw
     * @access public
     * @return string
     */
    public function normalizeMobile(string $raw): string
    {
        $digits = preg_replace('/\D+/', '', $raw);
        if(!is_string($digits) || $digits === '') return '';
        if(strlen($digits) > 11 && str_starts_with($digits, '86')) $digits = substr($digits, -11);
        return $digits;
    }

    /**
     * 按后 11 位筛候选，再在 PHP 中精确比对 mobile / phone。
     *
     * @param  string $normalized
     * @access public
     * @return array
     */
    public function findUsersByMobile(string $normalized): array
    {
        $suffix = strlen($normalized) >= 11 ? substr($normalized, -11) : $normalized;
        if($suffix === '') return array();

        $candidates = $this->dao->select('*')->from(TABLE_USER)
            ->where('deleted')->eq('0')
            ->andWhere('account')->ne('guest')
            ->andWhere('mobile', true)->like("%{$suffix}")
            ->orWhere('phone')->like("%{$suffix}")
            ->markRight(1)
            ->fetchAll();

        $matched = array();
        foreach($candidates as $user)
        {
            $mobile = $this->normalizeMobile((string)($user->mobile ?? ''));
            $phone  = $this->normalizeMobile((string)($user->phone ?? ''));
            if($mobile === $normalized || $phone === $normalized) $matched[] = $user;
        }
        return $matched;
    }

    /**
     * 校验 HS256 JWT，与 Hutool JWTUtil.createToken 对齐。
     *
     * @param  string $token
     * @param  string $secret
     * @access public
     * @return array|false
     */
    public function verifyJwt(string $token, string $secret)
    {
        $token = trim($token);
        if($token === '') return false;
        $parts = explode('.', $token);
        if(count($parts) !== 3) return false;

        list($header, $payload, $signature) = $parts;
        $expected = $this->base64UrlEncode(hash_hmac('sha256', $header . '.' . $payload, $secret, true));
        $given    = rtrim($signature, '=');
        if(!hash_equals($expected, $given)) return false;

        $json = $this->base64UrlDecode($payload);
        if($json === false) return false;
        $data = json_decode($json, true);
        return is_array($data) ? $data : false;
    }

    /**
     * @param  string $raw
     * @access protected
     * @return string
     */
    protected function base64UrlEncode(string $raw): string
    {
        return rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
    }

    /**
     * @param  string $encoded
     * @access protected
     * @return string|false
     */
    protected function base64UrlDecode(string $encoded)
    {
        $remainder = strlen($encoded) % 4;
        if($remainder > 0) $encoded .= str_repeat('=', 4 - $remainder);
        return base64_decode(strtr($encoded, '-_', '+/'));
    }
}
