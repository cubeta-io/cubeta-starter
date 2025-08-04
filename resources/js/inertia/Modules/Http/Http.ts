import ApiResponse from "@/Modules/Http/ApiResponse";

class HTTP<RESPONSE extends any = any> {
  private static instance: HTTP | undefined = undefined;
  private baseHeaders = {
    Accept: "application/html",
    "Accept-Language": window.localStorage.getItem("locale") ?? "en",
  };
  private isFile = false;

  private constructor() {}

  public static make<T extends any = any>(): HTTP<T> {
    if (!this.instance) {
      this.instance = new HTTP<T>();
    }

    this.instance.isFile = false;

    return this.instance as HTTP<T>;
  }

  public headers = (headers: Record<string, string>) => {
    this.baseHeaders = { ...this.baseHeaders, ...headers };
    return this;
  };

  public file() {
    this.isFile = true;
    return this;
  }

  public async get(
    url: string,
    params?: Record<string, any>,
    headers?: Record<string, string>,
  ): Promise<ApiResponse<RESPONSE | undefined>>;

  public async get(
    url: string,
    params?: Record<string, any>,
    headers?: Record<string, string>,
  ): Promise<Response>;

  public async get(
    url: string,
    params?: Record<string, any>,
    headers?: Record<string, string>,
  ): Promise<ApiResponse<RESPONSE | undefined> | Response> {
    return await this.run("GET", url, headers, params, undefined);
  }

  public async post(
    url: string,
    data: Record<string, any>,
    headers?: Record<string, string>,
  ): Promise<ApiResponse<RESPONSE | undefined>>;

  public async post(
    url: string,
    data: Record<string, any>,
    headers?: Record<string, string>,
  ): Promise<Response>;

  public async post(
    url: string,
    data: Record<string, any> = {},
    headers?: Record<string, string>,
  ): Promise<ApiResponse<RESPONSE | undefined> | Response> {
    return await this.run("POST", url, headers, undefined, data);
  }

  public async delete(
    url: string,
    headers?: Record<string, string>,
  ): Promise<ApiResponse<RESPONSE | undefined>>;

  public async delete(
    url: string,
    headers?: Record<string, string>,
  ): Promise<Response>;

  public async delete(
    url: string,
    headers?: Record<string, string>,
  ): Promise<ApiResponse<RESPONSE | undefined> | Response> {
    return await this.run("DELETE", url, headers, undefined);
  }

  public async put(
    url: string,
    data: Record<string, any>,
    headers?: Record<string, string>,
  ): Promise<ApiResponse<RESPONSE | undefined>>;

  public async put(
    url: string,
    data: Record<string, any>,
    headers?: Record<string, string>,
  ): Promise<Response>;

  public async put(
    url: string,
    data: Record<string, any> = {},
    headers?: Record<string, string>,
  ): Promise<ApiResponse<RESPONSE | undefined> | Response> {
    data = { ...data, _method: "PUT" };
    return await this.run("PUT", url, headers, undefined, data);
  }

  private run(
    method: string,
    url: string,
    headers?: Record<string, string>,
    params?: string | string[][] | Record<string, string> | URLSearchParams,
    data?: Record<string, any> | undefined,
  ): Promise<Response>;
  private run(
    method: string,
    url: string,
    headers?: Record<string, string>,
    params?: string | string[][] | Record<string, string> | URLSearchParams,
    data?: Record<string, any> | undefined,
  ): Promise<ApiResponse<RESPONSE>>;

  private async run<T>(
    method: string,
    url: string,
    headers?: Record<string, string>,
    params?: string | string[][] | Record<string, string> | URLSearchParams,
    data?: Record<string, any> | undefined,
  ): Promise<ApiResponse<T> | Response> {
    try {
      url = this.addParamsToUrl(params, url);
      url = this.getUrl(url);

      this.addCsrfIfNeeded(method);

      const config: RequestInit = {
        method: method,
        headers: {
          ...headers,
          ...this.baseHeaders,
        },
      };

      if ((method === "POST" || method === "PUT") && data) {
        if (this.shouldUseFormData(data)) {
          const form = new FormData();
          this.appendFormData(form, data);
          config.body = form;
          delete (config.headers as any)["Content-Type"];
        } else {
          config.body = JSON.stringify(data);
          config.headers = {
            ...config.headers,
            "Content-Type": "application/json",
          };
        }
      }

      const request = async () => await fetch(url, config);

      let response = await request();

      if (this.isFile) {
        return response;
      }

      let resData = await response.json();

      if (!response.ok) {
        console.log("Error : " + resData.message);
        console.log("Data : ", resData.data);
        console.log(resData);
      }

      return new ApiResponse(
        resData.data,
        resData.status,
        resData.code,
        resData.message,
        resData.pagination_data,
      );
    } catch (e) {
      console.error(e);
      console.error("Happened while requesting this url : " + url);
      return new ApiResponse(
        undefined,
        false,
        500,
        "Client error",
        undefined,
      );
    }
  }

  private addCsrfIfNeeded(method: string) {
    if (method == "POST" || method == "PUT" || method == "DELETE") {
      this.headers({
        "X-CSRF-TOKEN": HTTP.csrfToken() ?? "",
      });
    }
  }

  private getUrl = (url: string) => {
    try {
      const parsedUrl = new URL(url);
      parsedUrl.pathname = parsedUrl.pathname.replace(/\/{2,}/g, "/");
      return parsedUrl.toString();
    } catch (e) {
      console.error(e);
      return "";
    }
  };

  public static csrfToken(): string | null | undefined {
    return document
      ?.querySelector('meta[name="csrf-token"]')
      ?.getAttribute("content");
  }

  private addParamsToUrl(
    params:
      | string
      | string[][]
      | Record<string, string>
      | URLSearchParams
      | undefined,
    url: string,
  ) {
    if (
      params &&
      typeof params === "object" &&
      !(params instanceof URLSearchParams)
    ) {
      params = Object.fromEntries(
        Object.entries(params).filter(
          ([_, value]) => value !== undefined,
        ),
      );
      url =
        url +
        "?" +
        new URLSearchParams(params as Record<string, string>);
    }
    return url;
  }

  public async streamText(
    url: string,
    method: "GET" | "POST" = "POST",
    data?: Record<string, any>,
    headers?: Record<string, string>,
  ): Promise<AsyncGenerator<string, void, unknown>> {
    url = this.getUrl(url);
    this.addCsrfIfNeeded(method);

    const finalHeaders: HeadersInit = {
      ...this.baseHeaders,
      ...headers,
    };

    const config: RequestInit = {
      method,
      headers: finalHeaders,
    };

    if (method === "POST" && data) {
      const form = new FormData();
      Object.entries(data).forEach(([key, value]) => {
        form.append(key, value);
      });
      config.body = form;
    }

    const response = await fetch(url, config);

    if (!response.body) {
      throw new Error("Response body is not readable");
    }

    const reader = response.body.getReader();
    const decoder = new TextDecoder("utf-8");

    async function* streamGenerator(): AsyncGenerator<string> {
      let done = false;

      while (!done) {
        const { value, done: doneReading } = await reader.read();
        done = doneReading;

        const chunk = decoder.decode(value || new Uint8Array(), {
          stream: true,
        });
        if (chunk) yield chunk;
      }
    }

    return streamGenerator();
  }

  static async fileFromUrl(url: string, filename?: string): Promise<File> {
    const response = await fetch(url);
    const blob = await response.blob();
    const name = filename || url.split("/").pop() || "downloaded-file";
    return new File([blob], name, { type: blob.type });
  }

  private appendFormData(form: FormData, data: any, parentKey?: string) {
    if (data === null || data === undefined) return;

    if (Array.isArray(data)) {
      data.forEach((value, index) => {
        const key = parentKey
          ? `${parentKey}[${index}]`
          : String(index);
        this.appendFormData(
          form,
          value,
          parentKey ? `${parentKey}[]` : key,
        );
      });
    } else if (data instanceof File || data instanceof Blob) {
      form.append(parentKey!, data);
    } else if (typeof data === "object") {
      Object.entries(data).forEach(([key, value]) => {
        const formKey = parentKey ? `${parentKey}[${key}]` : key;
        this.appendFormData(form, value, formKey);
      });
    } else {
      form.append(parentKey!, String(data));
    }
  }

  private shouldUseFormData(data: any): boolean {
    if (data === null || data === undefined) return false;

    if (data instanceof File || data instanceof Blob) return true;

    if (typeof data === "object") {
      return Object.values(data).some((value) =>
        this.shouldUseFormData(value),
      );
    }

    return false;
  }
}

export default HTTP;
