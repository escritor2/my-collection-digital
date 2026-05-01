export type ApiErrorInfo = {
  status: number;
  message: string;
  fieldErrors: Record<string, string[]>;
};

export const useApiError = () => {
  const normalize = (error: any): ApiErrorInfo => {
    const status = Number(error?.response?.status || error?.status || 0);
    const payload = error?.response?._data || error?.data || {};
    const fieldErrors = (payload?.errors || {}) as Record<string, string[]>;

    if (status === 401) {
      return { status, message: 'Sua sessão expirou. Faça login novamente.', fieldErrors };
    }
    if (status === 403) {
      return { status, message: 'Você não tem permissão para esta ação.', fieldErrors };
    }
    if (status === 404) {
      return { status, message: 'Recurso não encontrado.', fieldErrors };
    }
    if (status === 419) {
      return { status, message: 'Sessão inválida. Tente novamente.', fieldErrors };
    }
    if (status === 422) {
      return { status, message: payload?.message || 'Verifique os campos e tente novamente.', fieldErrors };
    }
    if (status >= 500) {
      return { status, message: 'Erro interno no servidor. Tente novamente em instantes.', fieldErrors };
    }

    return {
      status,
      message: payload?.message || error?.message || 'Não foi possível concluir a operação.',
      fieldErrors,
    };
  };

  return { normalize };
};

