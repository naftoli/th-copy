async function streamChatGPTResponse(prompt, onChunk, onComplete, messages = []) {
    try {
        const formData = new FormData();
        formData.append('prompt', prompt);
        formData.append('history', JSON.stringify(messages));

        const response = await fetch('chatgpt_stream.php', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const reader = response.body.getReader();
        const decoder = new TextDecoder();
        let buffer = '';
        let fullResponse = '';

        while (true) {
            const { done, value } = await reader.read();
            
            if (done) {
                if (onComplete) {
                    onComplete(fullResponse);
                }
                break;
            }

            // Decode the chunk and add it to our buffer
            buffer += decoder.decode(value, { stream: true });
            
            // Process complete lines from the buffer
            const lines = buffer.split('\n');
            buffer = lines.pop() || '';

            for (const line of lines) {
                if (line.startsWith('data: ')) {
                    const data = line.slice(6);
                    if (data === '[DONE]') {
                        if (onComplete) {
                            onComplete(fullResponse);
                        }
                        return;
                    }

                    try {
                        const parsed = JSON.parse(data);
                        if (parsed.error) {
                            console.error('Error:', parsed.error);
                            return;
                        }
                        if (parsed.content) {
                            fullResponse += parsed.content;
                            if (onChunk) {
                                onChunk(parsed.content);
                            }
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                    }
                }
            }
        }
    } catch (error) {
        console.error('Error:', error);
        throw error;
    }
}

// Example usage:
/*
const prompt = "Tell me a short story";
const outputElement = document.getElementById('output');

streamChatGPTResponse(prompt, 
    (chunk) => {
        outputElement.textContent += chunk;
    },
    (completeResponse) => {
        console.log('Complete response:', completeResponse);
    },
    messages // Pass the conversation history
);
*/ 